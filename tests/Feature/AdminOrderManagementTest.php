<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public function test_only_admin_can_manage_orders_and_transition_in_sequence(): void
    {
        $customer = $this->customer();
        $order = $this->placedOrder($customer);
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->actingAs($customer)->get('/admin/orders')->assertForbidden();
        $this->patchJson('/admin/orders/'.$order->id.'/status', ['status' => 'Processing'])->assertForbidden();
        $this->actingAs($this->customer('Admin'))->get('/admin/orders')->assertOk()->assertSee($order->order_code);
        $this->get('/admin/orders/'.$order->id)->assertOk();
        foreach (['Processing', 'Shipped', 'Completed'] as $status) {
            $this->patch('/admin/orders/'.$order->id.'/status', ['status' => $status])
                ->assertRedirect('/admin/orders/'.$order->id);
            $this->assertSame($status, $order->fresh()->status);
        }
        $this->patchJson('/admin/orders/'.$order->id.'/status', ['status' => 'Cancelled'])->assertUnprocessable();
        $this->assertSame(8, Product::first()->stock);
    }

    public function test_cancel_restores_stock_once_and_final_states_are_immutable(): void
    {
        $order = $this->placedOrder($this->customer());
        $this->actingAs($this->customer('Admin'));
        $url = '/admin/orders/'.$order->id.'/status';
        $this->patchJson($url, ['status' => 'Completed'])->assertUnprocessable();
        $this->patch($url, ['status' => 'Cancelled'])->assertRedirect();
        $this->assertSame(10, Product::first()->stock);
        $this->patchJson($url, ['status' => 'Cancelled'])->assertUnprocessable();
        $this->patchJson($url, ['status' => 'Processing'])->assertUnprocessable();
        $this->assertSame(10, Product::first()->stock);
    }

    public function test_filter_search_pagination_invalid_status_and_missing_order(): void
    {
        $order = $this->placedOrder($this->customer());
        $this->actingAs($this->customer('Admin'));
        $this->get('/admin/orders?status=Pending&q='.urlencode($order->order_code))->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1);
        $this->get('/admin/orders?status=Completed')->assertOk()->assertDontSee($order->order_code);
        $this->getJson('/admin/orders?status=Invalid')->assertUnprocessable();
        $this->patchJson('/admin/orders/'.$order->id.'/status', ['status' => 'Invalid'])->assertUnprocessable();
        $this->get('/admin/orders/'.str_repeat('a', 24))->assertNotFound();
        $this->patchJson('/admin/orders/'.str_repeat('a', 24).'/status', ['status' => 'Processing'])->assertNotFound();
        for ($i = 0; $i < 11; $i++) {
            $copy = $order->replicate(['checkout_key']);
            $copy->order_code = 'ADMIN-'.$i;
            $copy->save();
        }
        $this->get('/admin/orders?status=Pending')->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 12 && $orders->count() === 10);
        $this->assertSame(12, Order::count());
    }

    public function test_cancellation_rolls_back_if_stock_cannot_be_restored(): void
    {
        $order = $this->placedOrder($this->customer());
        Product::first()->delete();
        $this->actingAs($this->customer('Admin'))->patchJson('/admin/orders/'.$order->id.'/status', ['status' => 'Cancelled'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertSame('Pending', $order->fresh()->status);
    }
}
