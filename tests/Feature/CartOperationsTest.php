<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Services\SalesService;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class CartOperationsTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public function test_add_accumulate_update_remove_and_clear_persist_in_mongodb(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->actingAs($user)->get('/cart')->assertOk()->assertSee('Giỏ hàng đang trống');
        foreach ([2, 1] as $quantity) {
            $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => $quantity])->assertRedirect('/cart');
        }
        $cart = app(SalesService::class)->cart($user);
        $this->assertSame(3, $cart->items[0]['quantity']);
        $this->assertSame($product->code, $cart->items[0]['product_code']);
        $this->get('/cart')->assertOk()->assertSee('1.050.000 đ');
        $this->patch('/cart/items/'.$product->code, ['quantity' => 4])->assertRedirect('/cart');
        $this->assertSame(4, $cart->fresh()->items[0]['quantity']);
        $this->delete('/cart/items/'.$product->code)->assertRedirect('/cart');
        $this->assertSame([], $cart->fresh()->items);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->delete('/cart')->assertRedirect('/cart');
        $this->assertSame([], $cart->fresh()->items);
        $this->assertSame(10, Product::find($product->id)->stock);
        $this->assertSame(1, Cart::count());
    }

    public function test_validation_rejects_invalid_quantities_ids_and_stock_overflow(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product(['stock' => 3]);
        foreach ([0, -1, 1.5, 1000, 'abc', []] as $quantity) {
            $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => $quantity])
                ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        }
        $this->postJson('/cart/add', ['product_id' => 'bad', 'quantity' => 1])->assertUnprocessable();
        $this->postJson('/cart/add', ['product_id' => str_repeat('a', 24), 'quantity' => 1])->assertNotFound();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->patchJson('/cart/items/'.$product->code, ['quantity' => 4])->assertUnprocessable();
        $this->assertSame(2, Cart::first()->items[0]['quantity']);
    }

    public function test_guests_are_blocked_and_carts_are_isolated(): void
    {
        $this->get('/cart')->assertRedirect('/login');
        $this->postJson('/cart/add', [])->assertUnauthorized();
        $first = $this->customer();
        $second = $this->customer();
        $product = $this->product();
        $this->actingAs($first)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($second)->get('/cart')->assertOk()->assertDontSee($product->name);
        $this->delete('/cart/items/'.$product->code)->assertNotFound();
        $this->assertSame(2, app(SalesService::class)->cart($first)->items[0]['quantity']);
    }

    public function test_unavailable_or_deleted_product_is_handled_without_hiding_cart(): void
    {
        $this->actingAs($this->customer());
        $product = $this->product(['status' => 'Hết hàng']);
        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertUnprocessable();
        $product->update(['status' => 'Còn hàng']);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $product->delete();
        $this->get('/cart')->assertOk()->assertSee('không còn tồn tại');
        $this->delete('/cart/items/'.$product->code)->assertRedirect('/cart');
    }
}
