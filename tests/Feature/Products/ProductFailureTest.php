<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductFailureTest extends ProductTestCase
{
    public function test_failed_database_write_preserves_old_image_and_cleans_new_upload(): void
    {
        Storage::fake('public');
        $category = $this->category();
        Storage::disk('public')->put('products/existing.jpg', 'original');
        $product = $this->product($category, ['image' => 'products/existing.jpg']);
        // Reject the next write at the real MongoDB validator, after FormRequest succeeds.
        DB::connection('mongodb')->getDatabase()->command([
            'collMod' => 'products', 'validator' => ['price' => ['$gte' => 1000000]],
        ]);
        $this->actingAs($this->actor())->putJson('/admin/products/'.$product->id, [
            'code' => $product->code, 'name' => 'Changed', 'category_id' => $category->id,
            'price' => 500000, 'stock' => 5, 'status' => 'Còn hàng',
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertStatus(503)->assertExactJson(['message' => 'Dữ liệu sản phẩm tạm thời không khả dụng. Vui lòng thử lại sau.']);
        $this->assertSame($product->name, $product->fresh()->name);
        $this->assertSame(['products/existing.jpg'], Storage::disk('public')->allFiles());
    }

    public function test_duplicate_race_is_reported_as_validation_and_upload_is_cleaned(): void
    {
        Storage::fake('public');
        $category = $this->category();
        // Simulate a concurrent insert after request uniqueness validation has passed.
        Product::creating(function (Product $product) {
            if ($product->code === 'RACE') {
                DB::connection('mongodb')->getDatabase()->products->insertOne([
                    'code' => 'RACE', 'name' => 'Concurrent product', 'slug' => 'concurrent',
                    'price' => 1, 'category' => ['code' => 'LH001', 'name' => 'Hoa Hồng', 'slug' => 'hoa-hong'],
                ]);
            }
        });
        $this->actingAs($this->actor())->postJson('/admin/products', [
            'code' => 'RACE', 'name' => 'New product', 'category_id' => $category->id,
            'price' => 500000, 'stock' => 5, 'status' => 'Còn hàng',
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->assertSame(1, Product::where('code', 'RACE')->count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_unavailable_mongodb_returns_generic_503_including_route_binding(): void
    {
        $actor = $this->actor();
        $dsn = config('database.connections.mongodb.dsn');
        try {
            config(['database.connections.mongodb.dsn' => 'mongodb://127.0.0.1:1/?serverSelectionTimeoutMS=50&connectTimeoutMS=50']);
            DB::purge('mongodb');
            $this->getJson('/api/products/search?q=hoa')->assertStatus(503)
                ->assertDontSee('127.0.0.1:1')->assertDontSee('MongoDB\\Driver');
            $this->actingAs($actor)->getJson('/admin/products/'.str_repeat('a', 24).'/edit')->assertStatus(503);
        } finally {
            config(['database.connections.mongodb.dsn' => $dsn]);
            DB::purge('mongodb');
        }
    }

    public function test_csrf_token_is_required_for_admin_mutations(): void
    {
        $product = $this->product($this->category());
        $this->actingAs($this->actor());
        // Enable the actual CSRF check normally bypassed by the Laravel test harness.
        $this->app->instance('env', 'local');
        $this->delete('/admin/products/'.$product->id)->assertStatus(419);
        $this->assertNotNull($product->fresh());
        $token = 'csrf-test-token';
        $this->withSession(['_token' => $token])->delete('/admin/products/'.$product->id, ['_token' => $token])
            ->assertRedirect('/admin/products');
        $this->assertNull(Product::find($product->id));
    }
}
