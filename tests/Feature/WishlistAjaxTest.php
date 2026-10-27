<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class WishlistAjaxTest extends TestCase
{
    use UsesMongoDatabase;

    private function makeProduct(): Product
    {
        return Product::create([
            'code' => 'SP1', 'name' => 'Hoa Tulip', 'price' => 300000, 'stock' => 10,
            'slug' => 'hoa-tulip', 'category' => ['code' => 'LH001', 'name' => 'Hoa Bó', 'slug' => 'hoa-bo'],
        ]);
    }

    public function test_authenticated_user_can_toggle_wishlist_via_ajax(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        // Toggle lần 1: Thêm vào wishlist
        $response1 = $this->actingAs($user)->postJson('/wishlist/toggle', ['product_code' => $product->code]);
        $response1->assertStatus(200)->assertJson(['status' => 'added', 'count' => 1]);
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_code' => 'SP1'], 'mongodb');

        // Toggle lần 2: Xóa khỏi wishlist
        $response2 = $this->actingAs($user)->postJson('/wishlist/toggle', ['product_code' => $product->code]);
        $response2->assertStatus(200)->assertJson(['status' => 'removed', 'count' => 0]);
        $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'product_code' => 'SP1'], 'mongodb');
    }

    public function test_guest_cannot_toggle_wishlist(): void
    {
        $this->postJson('/wishlist/toggle', ['product_code' => 'SP1'])->assertStatus(401);
    }

    public function test_toggle_rejects_unknown_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/wishlist/toggle', ['product_code' => 'KHONG_CO'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('product_code');
        $this->assertDatabaseCount('wishlists', 0, 'mongodb');
    }

    public function test_user_can_view_saved_products_on_wishlist_page(): void
    {
        $user = User::factory()->create();
        $this->makeProduct();
        Wishlist::create(['user_id' => $user->id, 'product_code' => 'SP1']);

        $this->actingAs($user)->get('/wishlist')
            ->assertStatus(200)
            ->assertSee('Sản phẩm yêu thích')
            ->assertSee('Hoa Tulip');
    }

    public function test_liked_product_shows_red_heart_and_navbar_count(): void
    {
        $user = User::factory()->create();
        $this->makeProduct();
        Wishlist::create(['user_id' => $user->id, 'product_code' => 'SP1']);

        $this->actingAs($user)->get('/')
            ->assertStatus(200)
            ->assertSee('images/heart-red.svg', false)
            ->assertSee('<span class="count_item count_item_pr js-wishlist-count">1</span>', false);
    }
}
