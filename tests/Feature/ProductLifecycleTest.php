<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductLifecycle;
use App\Services\SalesService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Process\Process;
use Tests\Concerns\SalesFixtures;
use Tests\TestCase;

class ProductLifecycleTest extends TestCase
{
    use SalesFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(app()->environment('testing'));
        $name = 'shop_hoatuoi_test_an12_'.bin2hex(random_bytes(10));
        config(['database.default' => 'mongodb', 'database.connections.mongodb.database' => $name]);
        DB::purge('mongodb');
        $db = DB::connection('mongodb')->getDatabase();
        $this->assertNotContains($name, iterator_to_array(DB::connection('mongodb')->getMongoClient()->listDatabaseNames()));
        $db->createCollection('_an12_owner');
        $db->_an12_owner->insertOne(['token' => bin2hex(random_bytes(16))]);
        // Kept for inspection: no drop/truncate cleanup in this suite.
        $this->artisan('migrate', ['--path' => 'database/migrations/mongodb', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
    }

    private function admin(): User
    {
        return $this->customer('Admin');
    }

    public function test_concurrent_delete_with_cart_wishlist_and_checkout_is_safe(): void
    {
        foreach (['add', 'wishlist', 'checkout'] as $action) {
            $p = $this->product();
            $user = $this->customer();
            if ($action === 'checkout') {
                app(SalesService::class)->change($user, 'add', $p->id);
            }
            $start = (string) (microtime(true) + 1);
            $processes = [];
            foreach (['delete', $action] as $job) {
                $process = new Process([
                    PHP_BINARY, base_path('tests/Fixtures/lifecycle-worker.php'), $job, $user->id, $p->id, $start,
                ], base_path(), [
                    'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mongodb',
                    'MONGODB_DATABASE' => DB::connection('mongodb')->getDatabaseName(),
                    'MONGODB_URI' => config('database.connections.mongodb.dsn'), 'XDEBUG_MODE' => 'off',
                ]);
                $process->start();
                $processes[] = $process;
            }
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $this->assertContains(trim($process->getOutput()), ['success', 'rejected']);
            }
            if (! $p->fresh()) {
                $db = DB::connection('mongodb')->getDatabase();
                $this->assertSame(0, $db->orders->countDocuments(['items.product_code' => $p->code]));
                $this->assertSame(0, $db->carts->countDocuments(['items.product_code' => $p->code]));
                $this->assertSame(0, $db->wishlists->countDocuments(['product_code' => $p->code]));
            } else {
                $this->assertGreaterThanOrEqual(0, $p->fresh()->stock);
            }
        }
    }

    public function test_catalog_filter_sort_pagination_and_ajax_keep_working(): void
    {
        $category = Category::create(['code' => 'AN12CAT', 'name' => 'AN12 Hoa', 'slug' => 'an12-hoa']);
        for ($i = 0; $i < 15; $i++) {
            $this->product(['name' => 'AN12 Catalog '.$i, 'price' => 100000 + $i, 'category' => $category->only(['code', 'name', 'slug'])]);
        }
        $response = $this->get('/products?category_slug=an12-hoa&sort=price-asc');
        $response->assertOk();
        $products = $response->viewData('products');
        $this->assertCount(12, $products);
        $this->assertSame(15, $products->total());
        $this->assertSame(range(100000, 100011), $products->pluck('price')->all());
        $this->assertStringContainsString('category_slug=an12-hoa', $products->url(2));
        $this->assertCount(3, $this->get('/products?category_slug=an12-hoa&page=2')->assertOk()->viewData('products'));
        $this->getJson('/api/products/search?q=AN12%20Catalog')->assertOk()->assertJsonCount(6, 'products');
    }

    public function test_admin_upload_replace_validation_and_safe_delete_still_work(): void
    {
        Storage::fake('public');
        $category = Category::create(['code' => 'AN12CAT', 'name' => 'Hoa', 'slug' => 'an12-hoa']);
        $this->actingAs($this->admin());
        $data = ['code' => 'AN12UPLOAD', 'name' => 'AN12 Upload', 'slug' => 'an12-upload', 'category_id' => $category->id,
            'price' => '123456.75', 'stock' => '15', 'status' => 'Còn hàng'];
        $this->postJson(route('admin.products.store'), $data + ['image' => UploadedFile::fake()->image('first.jpg')])->assertOk();
        $p = Product::where('code', 'AN12UPLOAD')->firstOrFail();
        $oldImage = $p->image;
        Storage::disk('public')->assertExists($oldImage);
        $this->putJson(route('admin.products.update', $p), $data + ['image' => UploadedFile::fake()->image('second.png')])->assertOk();
        $p = $p->fresh();
        $this->assertNotSame($oldImage, $p->image);
        Storage::disk('public')->assertMissing($oldImage);
        $this->putJson(route('admin.products.update', $p), array_replace($data, ['price' => -1]))->assertUnprocessable();
        $this->assertSame(123456.75, $p->fresh()->price);
        $this->deleteJson(route('admin.products.destroy', $p))->assertOk();
        Storage::disk('public')->assertMissing($p->image);
    }

    public function test_lifecycle_mutations_require_csrf(): void
    {
        $p = $this->product();
        $this->actingAs($this->admin());
        $this->app->instance('env', 'local');
        $this->postJson(route('admin.products.stop-selling', $p))->assertStatus(419);
        $this->postJson(route('admin.products.resume-selling', $p))->assertStatus(419);
        $this->deleteJson(route('admin.products.destroy', $p))->assertStatus(419);
        $this->assertTrue($p->fresh()->is_selling);
    }

    public function test_stop_and_resume_preserve_identity_stock_price_image_and_history(): void
    {
        $p = $this->product(['stock' => 15]);
        $db = DB::connection('mongodb')->getDatabase();
        $db->orders->insertOne(['items' => [['product_code' => $p->code, 'price' => 123, 'quantity' => 2]], 'status' => 'Cancelled']);
        $before = json_encode(iterator_to_array($db->orders->find()));
        $original = $p->only(['code', 'stock', 'price', 'image']);
        $this->actingAs($this->admin())->postJson(route('admin.products.stop-selling', $p))->assertOk();
        $this->assertFalse($p->fresh()->is_selling);
        $this->assertSame($original, $p->fresh()->only(array_keys($original)));
        $this->postJson(route('admin.products.resume-selling', $p))->assertOk();
        $this->assertTrue($p->fresh()->is_selling);
        $this->assertSame($original, $p->fresh()->only(array_keys($original)));
        $this->assertSame($before, json_encode(iterator_to_array($db->orders->find())));
    }

    public function test_order_states_and_legacy_references_block_direct_delete(): void
    {
        $p = $this->product();
        $this->actingAs($this->admin());
        $db = DB::connection('mongodb')->getDatabase();
        foreach (['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'] as $status) {
            foreach ([['product_code' => $p->code], ['product_code' => strtolower($p->code)], ['product_id' => $p->id], ['product_id' => new ObjectId($p->id)], ['MaSP' => $p->code]] as $reference) {
                $id = $db->orders->insertOne(['items' => [$reference], 'status' => $status])->getInsertedId();
                $this->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable()->assertJsonValidationErrors('product');
                $this->assertNotNull($p->fresh());
                // Only the fixture just inserted is removed, no collection reset.
                $db->orders->deleteOne(['_id' => $id]);
            }
        }
    }

    public function test_unknown_order_shape_and_reference_lookup_failure_are_closed(): void
    {
        $p = $this->product();
        $other = $this->product();
        $orders = DB::connection('mongodb')->getDatabase()->orders;
        $id = $orders->insertOne(['items' => [['product_code' => $other->code]]])->getInsertedId();
        $this->assertNull(app(ProductLifecycle::class)->deletionReason($p));
        $orders->updateOne(['_id' => $id], ['$set' => ['items' => [['product_code' => 'AN12-UNRESOLVED-LEGACY']]]]);
        $this->actingAs($this->admin())->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable();
        config(['database.default' => 'sqlite']);
        $this->assertNotNull(app(ProductLifecycle::class)->deletionReason($p));
        config(['database.default' => 'mongodb']);
        $orders->updateOne(['_id' => $id], ['$set' => ['items' => [['unknown_reference' => 'legacy']]]]);
        $this->actingAs($this->admin())->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable();
        $mock = \Mockery::mock(ProductLifecycle::class)->makePartial();
        $mock->shouldReceive('deletionReason')->andThrow(new \RuntimeException('simulated lookup failure'));
        $this->app->instance(ProductLifecycle::class, $mock);
        $this->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable();
        $this->assertNotNull($p->fresh());
    }

    public function test_legacy_order_details_cart_and_wishlist_are_not_cascaded(): void
    {
        $p = $this->product();
        $this->actingAs($this->admin());
        $db = DB::connection('mongodb')->getDatabase();
        foreach (['order_details', 'cart_items', 'wishlists'] as $collection) {
            $id = $db->$collection->insertOne(['product_id' => new ObjectId($p->id)])->getInsertedId();
            $this->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable();
            $this->assertSame(1, $db->$collection->countDocuments(['_id' => $id]));
            $db->$collection->deleteOne(['_id' => $id]);
        }
        $db->carts->insertOne(['items' => [['product_code' => $p->code, 'quantity' => 1]]]);
        $this->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable();
        $this->assertSame(1, $db->carts->countDocuments());
    }

    public function test_unreferenced_product_deletes_owned_image_but_not_shared_or_legacy(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        foreach (['products/owned.jpg', 'legacy.jpg', 'products/shared.jpg'] as $image) {
            Storage::disk('public')->put($image, 'fixture');
            $p = $this->product(['image' => $image]);
            if (str_contains($image, 'shared')) {
                $this->product(['image' => $image]);
            }
            $this->deleteJson(route('admin.products.destroy', $p))->assertOk();
            $this->assertNull($p->fresh());
            $this->assertSame($image !== 'products/owned.jpg', Storage::disk('public')->exists($image));
        }
    }

    public function test_guest_and_user_cannot_mutate_lifecycle(): void
    {
        $p = $this->product();
        $this->postJson(route('admin.products.stop-selling', $p))->assertUnauthorized();
        $this->postJson(route('admin.products.resume-selling', $p))->assertUnauthorized();
        $this->deleteJson(route('admin.products.destroy', $p))->assertUnauthorized();
        $this->actingAs($this->customer());
        $this->postJson(route('admin.products.stop-selling', $p))->assertForbidden();
        $this->postJson(route('admin.products.resume-selling', $p))->assertForbidden();
        $this->deleteJson(route('admin.products.destroy', $p))->assertForbidden();
        $this->assertSame(10, $p->fresh()->stock);
    }

    public function test_stopped_product_cannot_be_added_or_checked_out_and_is_hidden_from_search(): void
    {
        $p = $this->product();
        $user = $this->customer();
        $service = app(SalesService::class);
        $service->change($user, 'add', $p->id);
        $this->actingAs($this->admin())->postJson(route('admin.products.stop-selling', $p))->assertOk();
        $this->getJson('/api/products/search?q='.urlencode($p->name))->assertJsonCount(0, 'products');
        $this->get('/products')->assertDontSee($p->name);
        $this->get('/products/'.$p->slug)->assertOk()->assertSee('Ngừng bán');
        foreach (['add', 'checkout'] as $action) {
            try {
                $action === 'add' ? $service->change($user, 'add', $p->id) : $service->checkout($user, $this->delivery());
                $this->fail('Stopped product must not be purchasable.');
            } catch (ValidationException) {
                $this->assertSame(10, $p->fresh()->stock);
                $this->assertSame(0, Order::count());
            }
        }
    }

    public function test_resume_zero_stock_is_active_but_sold_out(): void
    {
        $p = $this->product(['stock' => 0]);
        $this->actingAs($this->admin())->postJson(route('admin.products.stop-selling', $p))->assertOk();
        $this->postJson(route('admin.products.resume-selling', $p))->assertOk();
        $this->assertTrue($p->fresh()->is_selling);
        $this->assertSame('Hết hàng', $p->fresh()->sale_label);
        $this->assertSame(0, $p->fresh()->stock);
    }

    public function test_admin_edit_preserves_sale_state_and_immutable_code(): void
    {
        $p = $this->product(['stock' => 15]);
        $category = Category::create(['code' => 'LH001', 'name' => 'Hoa', 'slug' => 'hoa']);
        $data = $p->only(['code', 'name', 'slug', 'price', 'stock', 'status']) + ['category_id' => $category->id];
        $this->actingAs($this->admin())->putJson(route('admin.products.update', $p), array_replace($data, ['name' => 'Tên mới', 'price' => 123456, 'stock' => 18]))->assertOk();
        $this->assertSame('Tên mới', $p->fresh()->name);
        $this->assertSame(18, $p->fresh()->stock);
        $this->putJson(route('admin.products.update', $p), array_replace($data, ['code' => 'CHANGED']))->assertUnprocessable();
        $this->postJson(route('admin.products.stop-selling', $p))->assertOk();
        $this->putJson(route('admin.products.update', $p), $data + ['is_selling' => true])->assertOk();
        $this->assertFalse($p->fresh()->is_selling);
    }
}
