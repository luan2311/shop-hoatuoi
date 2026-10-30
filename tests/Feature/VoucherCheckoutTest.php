<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class VoucherCheckoutTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public function test_cod_voucher_is_persisted_in_the_authoritative_order_total(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product(['price' => 700000]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 3])->assertRedirect();
        $this->post('/checkout', $this->delivery(['voucher_code' => 'HOATUOI2026', 'subtotal_amount' => 1, 'discount_amount' => 2100000, 'total_amount' => 0]))
            ->assertRedirect('/orders/success');
        $order = Order::firstOrFail();
        $this->assertSame(2050000, $order->total_amount);
        $this->assertSame(2100000, $order->subtotal_amount);
        $this->assertSame(50000, $order->discount_amount);
        $this->assertSame('HOATUOI2026', $order->voucher_code);
        $this->assertSame(700000, $order->items[0]['price']);
        $this->assertSame(7, $product->fresh()->stock);
        $document = DB::connection('mongodb')->getDatabase()->selectCollection('orders')->findOne(['_id' => new ObjectId($order->id)]);
        $this->assertSame(2050000, $document['total_amount']);
        $this->assertSame(50000, $document['discount_amount']);
        $this->get('/orders/success')->assertOk()->assertSee('2.050.000 đ')->assertSee('HOATUOI2026');
        $this->get('/account/orders')->assertOk()->assertSee('2.050.000 đ');
        $this->get('/account/orders/'.$order->id)->assertOk()->assertSee('2.100.000 đ')->assertSee('2.050.000 đ')->assertSee('50.000 đ');
        $this->actingAs($this->customer('Admin'))->get('/admin/orders/'.$order->id)
            ->assertOk()->assertSee('2.050.000 VNĐ')->assertSee('HOATUOI2026');
    }

    public function test_quote_is_server_calculated_repeatable_and_persists_after_refresh(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/checkout/voucher', ['voucher_code' => ' hoatuoi2026 ', 'discount_amount' => 700000, 'total_amount' => 1])
                ->assertOk()->assertJsonPath('subtotal_amount', 700000)->assertJsonPath('discount_amount', 50000)
                ->assertJsonPath('total_amount', 650000)->assertJsonPath('voucher_code', 'HOATUOI2026');
        }
        $this->get('/checkout')->assertOk()->assertViewHas('total', 650000)->assertSee('value="HOATUOI2026"', false);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(0, Order::count());
        $this->patch('/cart/items/'.$product->code, ['quantity' => 3])->assertRedirect();
        $this->get('/checkout')->assertOk()->assertViewHas('subtotal_amount', 1050000)->assertViewHas('total', 1000000);
        $product->update(['price' => 400000]);
        $this->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])
            ->assertOk()->assertJsonPath('subtotal_amount', 1200000)->assertJsonPath('total_amount', 1150000);
    }

    public function test_invalid_code_is_rejected_and_clears_the_previous_quote(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])->assertOk();
        $this->postJson('/checkout/voucher', ['voucher_code' => 'INVALID'])
            ->assertUnprocessable()->assertJsonValidationErrors('voucher_code')->assertJsonPath('total_amount', 350000)->assertJsonPath('voucher_code', null);
        $this->get('/checkout')->assertViewHas('total', 350000);
        $this->postJson('/checkout', $this->delivery(['voucher_code' => 'INVALID']))
            ->assertUnprocessable()->assertJsonValidationErrors('voucher_code');
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(1, Cart::first()->items[0]['quantity']);
    }

    public function test_no_voucher_and_freeship_do_not_inherit_a_previous_discount(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])->assertOk();
        $this->postJson('/checkout/voucher', ['voucher_code' => 'FREESHIP'])
            ->assertOk()->assertJsonPath('discount_amount', 0)->assertJsonPath('total_amount', 350000);
        $this->postJson('/checkout/voucher', ['voucher_code' => ''])->assertOk()->assertJsonPath('voucher_code', null);
        $this->post('/checkout', $this->delivery(['discount_amount' => 100000, 'total_amount' => 1]))->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(350000, $order->total_amount);
        $this->assertSame(0, $order->discount_amount);
        $this->assertNull($order->voucher_code);
    }

    public function test_checkout_revalidates_current_prices_and_caps_discount_at_subtotal(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])->assertOk();
        $product->update(['price' => 20000]);
        $this->post('/checkout', $this->delivery(['voucher_code' => 'HOATUOI2026']))->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(20000, $order->subtotal_amount);
        $this->assertSame(20000, $order->discount_amount);
        $this->assertSame(0, $order->total_amount);
    }

    public function test_retry_returns_original_discount_snapshot_without_touching_new_cart(): void
    {
        $user = $this->customer();
        $this->actingAs($user);
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $payload = $this->delivery(['voucher_code' => 'HOATUOI2026']);
        $this->post('/checkout', $payload)->assertRedirect();
        $original = Order::firstOrFail();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $product->update(['price' => 1]);
        $this->post('/checkout', array_merge($payload, ['voucher_code' => 'FREESHIP']))->assertRedirect();
        $this->assertSame(1, Order::count());
        $this->assertSame(650000, $original->fresh()->total_amount);
        $this->assertSame(50000, $original->fresh()->discount_amount);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(1, Cart::first()->items[0]['quantity']);
    }

    public function test_banking_qr_uses_confirmed_order_amount_and_never_marks_it_paid(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        foreach ([1, 2] as $quantity) {
            $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => $quantity]);
            $this->get('/checkout')->assertOk()->assertDontSee('amount=370000')->assertDontSee('127.0.0.1:8000');
            $this->post('/checkout', $this->delivery(['payment_method' => 'Banking', 'voucher_code' => 'HOATUOI2026']))->assertRedirect();
            $order = Order::orderBy('created_at', 'desc')->firstOrFail();
            $this->assertSame(350000 * $quantity - 50000, $order->total_amount);
            $this->assertSame('Pending', $order->status);
            $this->get('/orders/success')->assertOk()->assertSee('amount='.$order->total_amount)
                ->assertSee('addInfo='.$order->order_code)->assertSee('970422-0898449950-compact2.jpg');
        }
    }

    public function test_order_rejection_rolls_back_discount_stock_and_cart(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        DB::connection('mongodb')->getDatabase()->modifyCollection('orders', [
            'validator' => ['$jsonSchema' => ['bsonType' => 'object', 'required' => ['forced_missing_field']]],
        ]);
        $this->postJson('/checkout', $this->delivery(['voucher_code' => 'HOATUOI2026']))->assertStatus(503);
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(2, Cart::first()->items[0]['quantity']);
    }

    public function test_voucher_endpoint_requires_authentication_and_csrf_and_nonempty_cart(): void
    {
        $this->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])->assertUnauthorized();
        $this->actingAs($this->customer())->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])
            ->assertUnprocessable()->assertJsonValidationErrors('cart');
        $this->app->instance('env', 'local');
        try {
            $this->postJson('/checkout/voucher', ['voucher_code' => 'HOATUOI2026'])->assertStatus(419);
        } finally {
            $this->app->instance('env', 'testing');
        }
    }
}
