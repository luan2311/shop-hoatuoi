<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminProductTest extends ProductTestCase
{
    private function payload(string $categoryId): array
    {
        return ['code' => 'SP999', 'name' => 'Bó Hoa Tulip', 'category_id' => $categoryId,
            'price' => 500000, 'stock' => 20, 'status' => 'Còn hàng',
            'image' => UploadedFile::fake()->image('flower.jpg'), 'description' => 'Hoa đẹp'];
    }

    public function test_admin_crud_persists_bson_and_replaces_and_deletes_images(): void
    {
        Storage::fake('public');
        $category = $this->category();
        $this->actingAs($this->actor());
        $this->get('/admin/products/create')->assertOk()->assertSee('multipart/form-data', false);
        $this->post('/admin/products', $this->payload($category->id) + ['_id' => 'injected', 'is_admin' => true])
            ->assertRedirect('/admin/products');
        $product = Product::where('code', 'SP999')->firstOrFail();
        $this->assertSame(20, $product->stock);
        $this->assertSame(500000.0, $product->price);
        $this->assertSame('LH001', $product->category['code']);
        $this->assertSame($category->id, $product->categoryRecord->id);
        $this->assertNull($product->getAttribute('is_admin'));
        $this->assertNotSame('injected', $product->id);
        Storage::disk('public')->assertExists($oldImage = $product->image);
        $this->get('/admin/products')->assertOk()->assertSee('SP999');
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk();
        $data = $this->payload($category->id);
        $data['name'] = 'Tên mới';
        $data['price'] = 0;
        $this->put('/admin/products/'.$product->id, $data)->assertRedirect('/admin/products');
        $updated = $product->fresh();
        $this->assertSame('Tên mới', $updated->name);
        $this->assertSame($product->slug, $updated->slug);
        $this->assertSame(0.0, $updated->price);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($updated->image);
        unset($data['image']);
        $this->put('/admin/products/'.$product->id, $data)->assertRedirect('/admin/products');
        $this->assertSame($updated->image, $updated->fresh()->image);
        $this->delete('/admin/products/'.$product->id)->assertRedirect('/admin/products');
        $this->assertNull(Product::find($product->id));
        Storage::disk('public')->assertMissing($updated->image);
    }

    public function test_browser_numeric_strings_are_stored_as_bson_numbers(): void
    {
        Storage::fake('public');
        $category = $this->category();
        $payload = array_replace($this->payload($category->id), ['price' => '123456.75', 'stock' => '7']);
        $this->actingAs($this->actor())->post('/admin/products', $payload)->assertRedirect('/admin/products');
        $product = Product::where('code', 'SP999')->firstOrFail();
        $raw = \Illuminate\Support\Facades\DB::connection('mongodb')->getDatabase()->products->findOne(['code' => 'SP999']);
        $this->assertSame(123456.75, $raw['price']);
        $this->assertSame(7, $raw['stock']);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_guest_and_customer_cannot_access_admin_actions(): void
    {
        $product = $this->product($this->category());
        $paths = [['get', '/admin/products'], ['get', '/admin/products/create'], ['get', '/admin/products/'.$product->id.'/edit'],
            ['post', '/admin/products'], ['put', '/admin/products/'.$product->id], ['delete', '/admin/products/'.$product->id]];
        foreach ($paths as [$method, $path]) {
            $this->$method($path)->assertRedirect('/login');
        }
        $this->actingAs($this->actor(false));
        foreach ($paths as [$method, $path]) {
            $this->$method($path)->assertForbidden();
        }
        $this->assertSame(1, Product::count());
    }

    public function test_validation_rejects_missing_invalid_and_duplicate_data_without_writing_files(): void
    {
        Storage::fake('public');
        $category = $this->category();
        $existing = $this->product($category, ['code' => 'SP999']);
        $this->actingAs($this->actor());
        $this->postJson('/admin/products', [])->assertUnprocessable()->assertJsonValidationErrors(['code', 'name', 'price', 'stock', 'category_id', 'status', 'image']);
        $this->postJson('/admin/products', ['name' => ['bad'], 'code' => ['bad']])->assertUnprocessable()->assertJsonValidationErrors(['name', 'code']);
        $payload = $this->payload($category->id);
        $payload['slug'] = $existing->slug;
        $this->postJson('/admin/products', $payload)->assertUnprocessable()->assertJsonValidationErrors(['code', 'slug']);
        $payload = array_replace($payload, ['code' => 'SP2', 'slug' => 'new-product', 'price' => -1, 'stock' => 1.5,
            'category_id' => str_repeat('a', 24), 'status' => 'Invalid', 'image' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')]);
        $this->postJson('/admin/products', $payload)->assertUnprocessable()->assertJsonValidationErrors(['price', 'stock', 'category_id', 'status', 'image']);
        $payload = array_replace($this->payload($category->id), ['code' => 'SP2', 'image' => UploadedFile::fake()->image('big.jpg')->size(2049)]);
        $this->postJson('/admin/products', $payload)->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->assertSame(1, Product::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_unknown_product_ids_return_404(): void
    {
        $this->actingAs($this->actor());
        $this->get('/admin/products/'.str_repeat('a', 24).'/edit')->assertNotFound();
        $this->put('/admin/products/'.str_repeat('a', 24), [])->assertNotFound();
        $this->delete('/admin/products/'.str_repeat('a', 24))->assertNotFound();
        $this->get('/admin/products/not-an-id/edit')->assertNotFound();
    }

    public function test_html_is_escaped_in_admin_form_and_seed_image_is_never_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('legacy.jpg', 'keep');
        $product = $this->product($this->category(), ['name' => '<script>alert(1)</script>', 'image' => 'legacy.jpg']);
        $this->actingAs($this->actor())->get('/admin/products/'.$product->id.'/edit')
            ->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->delete('/admin/products/'.$product->id)->assertRedirect();
        Storage::disk('public')->assertExists('legacy.jpg');
    }
}
