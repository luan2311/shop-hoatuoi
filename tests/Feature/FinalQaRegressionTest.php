<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\SalesService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class FinalQaRegressionTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public static function completionStates(): array
    {
        return [['Pending', false], ['Processing', false], ['Shipped', true], ['Cancelled', false], ['Completed', false]];
    }

    #[DataProvider('completionStates')]
    public function test_post_and_patch_completion_follow_same_workflow(string $state, bool $allowed): void
    {
        $admin = $this->customer('Admin');
        $order = $this->placedOrder($admin);
        if ($state === 'Cancelled') {
            app(SalesService::class)->transition($order->id, 'Cancelled');
        } else {
            $order->update(['status' => $state]);
        }
        $stock = Product::first()->stock;
        $this->actingAs($admin);

        $post = $this->postJson('/admin/orders/'.$order->id.'/complete');
        if ($allowed) {
            $post->assertRedirect();
            $this->assertSame('Completed', $order->fresh()->status);
        } else {
            $post->assertUnprocessable()->assertJsonValidationErrors('status');
            $this->patchJson('/admin/orders/'.$order->id.'/status', ['status' => 'Completed'])
                ->assertUnprocessable()->assertJsonValidationErrors('status');
            $this->assertSame($state, $order->fresh()->status);
        }
        $this->assertSame($stock, Product::first()->stock);
    }

    public function test_unconfigured_payment_and_voucher_are_rejected_without_writes(): void
    {
        $user = $this->customer();
        $product = $this->product();
        app(SalesService::class)->change($user, 'add', $product->id, 2);
        $items = Cart::first()->items;
        $this->actingAs($user);

        $this->postJson('/checkout', $this->delivery(['payment_method' => 'Banking']))
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        foreach (['HOATUOI2026', 'INVALID'] as $voucher) {
            $this->postJson('/checkout', $this->delivery(['voucher_code' => $voucher]))
                ->assertUnprocessable()->assertJsonValidationErrors('voucher_code');
        }
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame($items, Cart::first()->items);
    }

    public function test_checkout_ui_has_only_cod_and_no_fake_discount_or_qr_for_different_totals(): void
    {
        $user = $this->customer();
        $product = $this->product(['price' => 125000]);
        $this->actingAs($user);
        foreach ([1, 2] as $quantity) {
            app(SalesService::class)->change($user, 'add', $product->id, $quantity);
            $this->get('/checkout')->assertOk()->assertSee('Chuyển khoản chưa được hỗ trợ')
                ->assertSee('Mã ưu đãi chưa được hỗ trợ')->assertDontSee('api.vietqr.io')
                ->assertDontSee('HOATUOI2026')->assertDontSee('value="Banking"', false);
        }
    }

    public function test_dashboard_total_includes_all_order_states(): void
    {
        $admin = $this->customer('Admin');
        foreach (['Pending', 'Processing', 'Shipped', 'Cancelled', 'Completed'] as $state) {
            $this->placedOrder($admin)->update(['status' => $state]);
        }

        $response = $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $response->assertViewHas('totalOrders', 5)->assertViewHas('completedOrders', 1);
        $this->assertMatchesRegularExpression('/text-value">5<\/div>\s*<div class="text-muted">Tổng số đơn/u', $response->getContent());
    }
}
