<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public function test_checkout_uses_database_prices_snapshots_stock_and_embedded_items(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->get('/checkout')->assertOk()->assertSee('700.000 đ');
        $this->post('/checkout', $this->delivery(['total_amount' => 1, 'price' => 1, 'user_id' => 'attacker']))
            ->assertRedirect('/orders/success');
        $order = Order::first();
        $this->assertSame(700000, $order->total_amount);
        $this->assertSame('Pending', $order->status);
        $this->assertSame($user->id, (string) $order->user_id);
        $this->assertSame($user->id, $order->user->id);
        $this->assertCount(1, $user->orders);
        $this->assertSame($user->id, $user->cart->user->id);
        $this->assertSame(350000, $order->items[0]['price']);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame([], Cart::first()->items);
        $document = DB::connection('mongodb')->getDatabase()->selectCollection('orders')->findOne(['_id' => new ObjectId($order->id)]);
        $this->assertSame(700000, $document['total_amount']);
        $this->assertInstanceOf(ObjectId::class, $document['user_id']);
        $this->get('/orders/success')->assertOk()->assertSee($order->order_code);
        $product->update(['name' => 'Tên mới', 'price' => 1]);
        $product->delete();
        $this->get('/account/orders/'.$order->id)->assertOk()->assertSee('Giỏ hoa thử nghiệm')->assertSee('700.000 đ');
        $this->postJson('/checkout', $this->delivery())->assertUnprocessable();
        $this->assertSame(1, Order::count());
    }

    public function test_partial_stock_changes_are_rolled_back_if_later_item_fails(): void
    {
        $this->actingAs($this->customer());
        $first = $this->product();
        $second = $this->product();
        foreach ([$first, $second] as $product) {
            $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        }
        $second->update(['stock' => 1]);
        $this->postJson('/checkout', $this->delivery())->assertUnprocessable();
        $this->assertSame(10, $first->fresh()->stock);
        $this->assertSame(1, $second->fresh()->stock);
        $this->assertSame(0, Order::count());
        $this->assertCount(2, Cart::first()->items);
    }

    public function test_database_rejection_rolls_back_order_stock_and_cart(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        // Force a real MongoDB schema failure after the service has decremented stock.
        DB::connection('mongodb')->getDatabase()->modifyCollection('orders', [
            'validator' => ['$jsonSchema' => ['bsonType' => 'object', 'required' => ['forced_missing_field']]],
        ]);
        $this->postJson('/checkout', $this->delivery())->assertStatus(503)->assertJsonStructure(['message']);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(0, Order::count());
        $this->assertSame(2, Cart::first()->items[0]['quantity']);
    }

    public function test_empty_cart_deleted_product_and_invalid_delivery_are_rejected(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
        $this->postJson('/checkout', $this->delivery())->assertUnauthorized();
        $this->actingAs($this->customer())->get('/checkout')->assertRedirect('/cart');
        $this->postJson('/checkout', $this->delivery())->assertUnprocessable()->assertJsonValidationErrors('cart');
        $this->postJson('/checkout', $this->delivery([
            'receiver_name' => '', 'receiver_phone' => '123', 'receiver_address' => '',
            'payment_method' => 'CreditCard', 'email' => 'bad', 'note' => str_repeat('x', 1001),
        ]))->assertUnprocessable()->assertJsonValidationErrors([
            'receiver_name', 'receiver_phone', 'receiver_address', 'payment_method', 'email', 'note',
        ]);
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $product->delete();
        $this->postJson('/checkout', $this->delivery())->assertUnprocessable()->assertJsonValidationErrors('cart');
        $this->assertSame(0, Order::count());
    }

    public function test_current_price_is_used_and_invalid_price_cannot_create_an_order(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $product->update(['price' => -1]);
        $this->postJson('/checkout', $this->delivery())->assertUnprocessable();
        $this->assertSame(10, $product->fresh()->stock);
        $product->update(['price' => 400000]);
        $this->post('/checkout', $this->delivery(['payment_method' => 'Banking']))->assertRedirect();
        $this->assertSame(400000, Order::first()->total_amount);
        $this->assertSame('Banking', Order::first()->payment_method);
    }

    public function test_history_is_owned_paginated_and_supports_legacy_string_user_ids(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $order = $this->placedOrder($owner);
        $this->actingAs($other)->get('/account/orders/'.$order->id)->assertNotFound();
        $this->get('/account/orders')->assertOk()->assertDontSee($order->order_code);
        $this->get('/orders/success')->assertNotFound();
        $this->get('/account/orders/invalid')->assertNotFound();
        $this->get('/account/orders/'.str_repeat('a', 24))->assertNotFound();
        $order->user_id = $owner->id;
        $order->save();
        for ($i = 0; $i < 11; $i++) {
            $copy = $order->replicate(['checkout_key']);
            $copy->order_code = 'HISTORY-'.$i;
            $copy->save();
        }
        $this->actingAs($owner)->get('/account/orders')->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 12 && $orders->count() === 10);
        $this->get('/account/orders/'.$order->id)->assertOk();
    }

    public function test_csrf_is_enforced_for_cart_and_checkout(): void
    {
        $this->actingAs($this->customer());
        // Laravel skips CSRF in testing; switch environment solely for these HTTP checks.
        $this->app->instance('env', 'local');
        try {
            $this->post('/cart/add', [])->assertStatus(419);
            $this->post('/checkout', [])->assertStatus(419);
        } finally {
            $this->app->instance('env', 'testing');
        }
    }
}
