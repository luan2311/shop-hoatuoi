<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Services\SalesService;
use Illuminate\Support\Str;
use MongoDB\BSON\Decimal128;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class SalesFinalAuditTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public function test_replaying_checkout_does_not_purchase_a_refilled_cart(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $request = $this->delivery(['checkout_token' => (string) Str::uuid()]);
        $this->post('/checkout', $request)->assertRedirect('/orders/success');
        $first = Order::firstOrFail();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/checkout', $request)->assertRedirect('/orders/success');
        $this->assertSame(1, Order::count());
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(1, Cart::first()->items[0]['quantity']);
        $this->assertSame($first->id, session('sales.last_order_id'));
    }

    public function test_checkout_token_is_required_and_scoped_to_each_customer(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        $token = (string) Str::uuid();
        $product = $this->product();
        foreach ([$first, $second] as $user) {
            $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
            $this->postJson('/checkout', $this->delivery(['checkout_token' => null]))->assertUnprocessable()
                ->assertJsonValidationErrors('checkout_token');
            $this->postJson('/checkout', $this->delivery(['checkout_token' => 'bad']))->assertUnprocessable();
            $this->post('/checkout', $this->delivery(['checkout_token' => $token]))->assertRedirect('/orders/success');
        }
        $this->assertSame(2, Order::count());
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_client_cannot_choose_another_cart_owner_or_edit_another_cart(): void
    {
        $owner = $this->customer();
        $attacker = $this->customer();
        $product = $this->product();
        $sales = app(SalesService::class);
        $sales->change($owner, 'add', $product->id, 2);
        $cart = $sales->cart($owner);
        $this->actingAs($attacker)->post('/cart/add', [
            'product_id' => $product->id, 'quantity' => 1,
            'user_id' => $owner->id, 'cart_id' => $cart->id, 'price' => 1,
        ])->assertRedirect('/cart');
        $this->patch('/cart/items/'.$product->code, [
            'quantity' => 3, 'user_id' => $owner->id, 'cart_id' => $cart->id,
        ])->assertRedirect('/cart');
        $this->assertSame(2, $cart->fresh()->items[0]['quantity']);
        $this->assertSame(3, $sales->cart($attacker)->items[0]['quantity']);
        $this->delete('/cart/items/'.$product->code)->assertRedirect('/cart');
        $this->assertSame(2, $cart->fresh()->items[0]['quantity']);
    }

    public function test_mongodb_decimal_product_prices_render_and_checkout_correctly(): void
    {
        $product = $this->product(['price' => new Decimal128('350000')]);
        $this->actingAs($this->customer())->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->get('/cart')->assertOk()->assertSee('350.000 đ')->assertSee('700.000 đ');
        $this->post('/checkout', $this->delivery())->assertRedirect('/orders/success');
        $this->assertSame(700000, Order::first()->total_amount);
        $this->assertSame(350000, Order::first()->items[0]['price']);
    }

    public function test_legacy_decimal_order_amounts_render_without_changing_stored_data(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $items = $order->items;
        $items[0]['price'] = new Decimal128('350000');
        $items[0]['subtotal'] = new Decimal128('700000');
        $order->update(['total_amount' => new Decimal128('700000'), 'items' => $items]);
        $this->actingAs($user)->get('/account/orders')->assertOk()->assertSee('700.000 đ');
        $this->get('/account/orders/'.$order->id)->assertOk()->assertSee('700.000 đ')->assertSee('350.000 đ');
        $this->actingAs($this->customer('Admin'))->get('/admin/orders')->assertOk()->assertSee('700.000 đ');
        $this->assertInstanceOf(Decimal128::class, $order->fresh()->total_amount);
    }
}
