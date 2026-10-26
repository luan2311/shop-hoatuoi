<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\SalesService;
use App\Support\Seeding\FlowerDataset;
use App\Support\Seeding\SafeFlowerImporter;
use App\Support\Seeding\SampleImportException;
use Database\Seeders\FlowerSampleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Tests\Concerns\UsesDungMongoDatabase;
use Tests\TestCase;

class SeederIntegrityTest extends TestCase
{
    use UsesDungMongoDatabase;

    private function target(): string
    {
        return config('database.connections.mongodb.database');
    }

    private function importer(): SafeFlowerImporter
    {
        return app(SafeFlowerImporter::class);
    }

    private function snapshot(string $collection): string
    {
        $rows = DB::connection('mongodb')->getDatabase()->selectCollection($collection)->find([], ['sort' => ['_id' => 1]])->toArray();

        return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
    }

    private function delivery(): array
    {
        return ['checkout_token' => (string) Str::uuid(), 'receiver_name' => 'QA Dũng',
            'receiver_phone' => '0901234567', 'receiver_address' => 'Địa chỉ kiểm thử', 'payment_method' => 'COD'];
    }

    public function test_full_dataset_is_inserted_on_a_real_isolated_mongodb_database(): void
    {
        $result = $this->importer()->run($this->target(), true);
        $this->assertSame('PASS', $result['status']);
        $db = DB::connection('mongodb')->getDatabase();
        foreach (['categories' => 5, 'products' => 80, 'posts' => 3] as $name => $count) {
            $this->assertSame($count, $db->selectCollection($name)->countDocuments());
            $this->assertSame($count, $result['collections'][$name]['inserted']);
            $this->assertSame(0, $result['collections'][$name]['updated']);
        }
        foreach ($db->products->find() as $product) {
            $this->assertInstanceOf(ObjectId::class, $product['_id']);
            $this->assertIsInt($product['price']);
            $this->assertIsInt($product['stock']);
            $this->assertGreaterThanOrEqual(0, $product['stock']);
            $this->assertSame('Còn hàng', $product['status']);
            $this->assertTrue(FlowerDataset::validImage($product['image']));
            $category = $db->categories->findOne(['code' => $product['category']['code']]);
            $this->assertNotNull($category);
            foreach (['code', 'name', 'slug'] as $field) {
                $this->assertSame($category[$field], $product['category'][$field]);
            }
        }
        $this->assertInstanceOf(UTCDateTime::class, $db->posts->findOne()['published_at']);
        $this->assertSame(0, $db->users->countDocuments());
        $this->assertSame(0, $db->orders->countDocuments());
        $this->assertSame(0, $db->carts->countDocuments());
        foreach (['code', 'slug'] as $field) {
            $this->assertCount(80, $db->products->distinct($field));
        }
        $text = array_filter(iterator_to_array($db->products->listIndexes()), fn ($index) => isset($index['weights']));
        $this->assertCount(1, $text);
    }

    public function test_second_import_preserves_every_document_including_ids_and_timestamps(): void
    {
        $this->importer()->run($this->target(), true);
        $before = array_map(fn ($name) => $this->snapshot($name), ['categories', 'products', 'posts']);
        $again = $this->importer()->run($this->target(), true);
        $this->assertSame($before, array_map(fn ($name) => $this->snapshot($name), ['categories', 'products', 'posts']));
        $this->assertSame([5, 80, 3], array_column($again['collections'], 'skipped'));
        $this->assertSame([0, 0, 0], array_column($again['collections'], 'inserted'));
    }

    public function test_reimport_after_real_checkout_keeps_orders_carts_users_and_reduced_stock(): void
    {
        $this->importer()->run($this->target(), true);
        $buyer = User::factory()->create();
        $product = Product::firstOrFail();
        $sales = app(SalesService::class);
        $sales->change($buyer, 'add', $product->id, 2);
        $order = $sales->checkout($buyer, $this->delivery());
        $this->assertSame(2 * $product->price, $order->total_amount);
        $this->assertSame($product->stock - 2, $product->fresh()->stock);
        $sales->change($buyer, 'add', $product->id, 1);
        $before = array_map(fn ($name) => $this->snapshot($name), ['orders', 'carts', 'users', 'products']);
        $this->importer()->run($this->target(), true);
        $this->assertSame($before, array_map(fn ($name) => $this->snapshot($name), ['orders', 'carts', 'users', 'products']));
    }

    public function test_preexisting_product_content_and_stock_are_never_overwritten(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $existing = app(FlowerDataset::class)->load()['collections']['products'][0];
        $existing['stock'] = 3;
        $existing['price'] = 123000;
        $existing['name'] = 'Sản phẩm của An đã sửa';
        $db->products->insertOne($existing);
        $result = $this->importer()->run($this->target(), true);
        $preserved = $db->products->findOne(['code' => $existing['code']]);
        $this->assertSame($existing['name'], $preserved['name']);
        $this->assertSame(3, $preserved['stock']);
        $this->assertSame(123000, $preserved['price']);
        $this->assertArrayNotHasKey('sample_data', $preserved);
        $this->assertSame(79, $result['collections']['products']['inserted']);
        $this->assertSame(1, $result['collections']['products']['skipped']);
    }

    public function test_existing_category_is_preserved_and_new_products_use_its_embedded_snapshot(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $category = app(FlowerDataset::class)->load()['collections']['categories'][0];
        $category['name'] = 'Danh mục do Luân đổi tên';
        $db->categories->insertOne($category);
        $this->importer()->run($this->target(), true);
        $this->assertSame($category['name'], $db->categories->findOne(['code' => $category['code']])['name']);
        foreach ($db->products->find(['category.code' => $category['code']]) as $product) {
            $this->assertSame($category['name'], $product['category']['name']);
        }
    }

    public function test_preview_does_not_write_documents_or_indexes(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $indexes = iterator_count($db->products->listIndexes());
        $result = $this->importer()->run($this->target());
        $this->assertSame('PREVIEW', $result['status']);
        $this->assertSame(80, $result['collections']['products']['would_insert']);
        $this->assertSame(0, $db->products->countDocuments());
        $this->assertSame(0, $db->categories->countDocuments());
        $this->assertSame(0, $db->posts->countDocuments());
        $this->assertSame($indexes, iterator_count($db->products->listIndexes()));
    }

    public function test_existing_slug_conflict_stops_before_any_new_data_is_inserted(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $product = app(FlowerDataset::class)->load()['collections']['products'][0];
        $product['code'] = 'AN-EXISTING';
        $db->products->insertOne($product);
        $before = $this->snapshot('products');
        try {
            $this->importer()->run($this->target(), true);
            $this->fail('Slug xung đột phải bị từ chối.');
        } catch (InvalidArgumentException) {
            $this->assertSame($before, $this->snapshot('products'));
            $this->assertSame(0, $db->categories->countDocuments());
            $this->assertSame(0, $db->posts->countDocuments());
        }
    }

    public function test_production_environment_is_rejected(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->run($this->target(), true);
    }

    public function test_shared_database_is_rejected_before_connecting_to_it(): void
    {
        config(['database.connections.mongodb.database' => 'shop_hoatuoi']);
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->run('shop_hoatuoi', true);
    }

    public function test_confirmation_must_match_the_configured_database(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->run('shop_hoatuoi_test_wrong_target', true);
    }

    public function test_sql_connection_cannot_be_used_as_fallback(): void
    {
        config(['database.default' => 'sqlite']);
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->run($this->target(), true);
    }

    public function test_remote_database_server_is_rejected_before_connecting(): void
    {
        config(['database.connections.mongodb.dsn' => 'mongodb://remote.example.test:27017']);
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->run($this->target(), true);
    }

    public function test_demo_users_have_real_hashes_and_can_authenticate_with_mongodb(): void
    {
        $passwords = ['User' => bin2hex(random_bytes(16)), 'Admin' => bin2hex(random_bytes(16))];
        $result = $this->importer()->run($this->target(), true, $passwords);
        $this->assertSame(2, $result['collections']['users']['inserted']);
        foreach ($passwords as $role => $password) {
            $user = User::where('email', strtolower($role).'.dung@example.test')->firstOrFail();
            $this->assertTrue(Hash::check($password, $user->password));
            $this->assertTrue($user->hasRole($role));
            $this->assertTrue(auth()->attempt(['email' => $user->email, 'password' => $password]));
            auth()->logout();
        }
        $encoded = json_encode($result);
        foreach ($passwords as $password) {
            $this->assertStringNotContainsString($password, $encoded);
        }
    }

    public function test_reimport_never_changes_existing_demo_user_password_role_or_profile(): void
    {
        $passwords = ['User' => bin2hex(random_bytes(16)), 'Admin' => bin2hex(random_bytes(16))];
        $this->importer()->run($this->target(), true, $passwords);
        $db = DB::connection('mongodb')->getDatabase();
        $db->users->updateOne(['email' => 'admin.dung@example.test'], ['$set' => ['roles' => ['User'], 'name' => 'Đã đổi', 'is_active' => false]]);
        $before = $this->snapshot('users');
        $again = $this->importer()->run($this->target(), true, ['User' => bin2hex(random_bytes(16)), 'Admin' => bin2hex(random_bytes(16))]);
        $this->assertSame($before, $this->snapshot('users'));
        $this->assertSame(2, $again['collections']['users']['skipped']);
        $this->assertSame(0, $again['collections']['users']['inserted']);
    }

    public function test_missing_demo_passwords_are_rejected_before_catalog_writes(): void
    {
        try {
            $this->importer()->run($this->target(), true, ['User' => 'short']);
            $this->fail('Thiếu mật khẩu phải bị từ chối.');
        } catch (InvalidArgumentException) {
            $this->assertSame(0, Product::count());
        }
    }

    public function test_unavailable_mongodb_returns_sanitized_failure_with_progress(): void
    {
        config(['database.connections.mongodb.dsn' => 'mongodb://127.0.0.1:1/?serverSelectionTimeoutMS=100']);
        DB::purge('mongodb');
        try {
            $this->importer()->run($this->target(), true);
            $this->fail('Kết nối hỏng phải báo lỗi.');
        } catch (SampleImportException $exception) {
            $this->assertSame('FAIL', $exception->summary['status']);
            $this->assertSame(0, $exception->summary['collections']['products']['inserted']);
            $this->assertStringNotContainsString('mongodb://', $exception->getMessage());
        }
    }

    public function test_database_rejection_reports_partial_progress_and_retry_keeps_inserted_records(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $source = app(FlowerDataset::class)->load()['collections']['products'];
        $schemas = json_decode(file_get_contents(base_path('tests/Fixtures/mongodb-validators.json')), true, flags: JSON_THROW_ON_ERROR);
        // Chỉ thay validator của database ngẫu nhiên do test này tạo, không dùng DB chung.
        $db->command(['collMod' => 'products', 'validator' => ['$and' => [
            $schemas['products']['validator'], ['code' => ['$ne' => $source[1]['code']]],
        ]]])->toArray();
        try {
            $this->importer()->run($this->target(), true);
            $this->fail('Validator phải ngăn sản phẩm thứ hai.');
        } catch (SampleImportException $exception) {
            $this->assertSame('FAIL', $exception->summary['status']);
            $this->assertSame(5, $exception->summary['collections']['categories']['inserted']);
            $this->assertSame(1, $exception->summary['collections']['products']['inserted']);
            $this->assertSame(0, $exception->summary['collections']['posts']['inserted']);
            $this->assertSame(1, Product::count());
        }
        $before = json_encode($db->products->findOne(['code' => $source[0]['code']]), JSON_THROW_ON_ERROR);
        $db->command(['collMod' => 'products', 'validator' => $schemas['products']['validator']])->toArray();
        $retry = $this->importer()->run($this->target(), true);
        $this->assertSame(79, $retry['collections']['products']['inserted']);
        $this->assertSame(1, $retry['collections']['products']['skipped']);
        $this->assertSame(80, Product::count());
        $this->assertSame($before, json_encode($db->products->findOne(['code' => $source[0]['code']]), JSON_THROW_ON_ERROR));
    }

    public function test_command_previews_by_default_and_applies_only_with_explicit_option(): void
    {
        $this->artisan('flowers:import-samples', ['--target' => $this->target()])->expectsOutputToContain('"status":"PREVIEW"')->assertExitCode(0);
        $this->assertSame(0, Product::count());
        $this->artisan('flowers:import-samples', ['--target' => $this->target(), '--apply' => true])->expectsOutputToContain('"inserted":80')->assertExitCode(0);
        $this->assertSame(80, Product::count());
    }

    public function test_command_without_target_refuses_to_write(): void
    {
        $this->artisan('flowers:import-samples', ['--apply' => true])->assertExitCode(1);
        $this->assertSame(0, Product::count());
    }

    public function test_db_seed_adapter_requires_explicit_apply_opt_in(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->seed(FlowerSampleSeeder::class);
    }

    public function test_db_seed_adapter_can_import_and_repeat_with_explicit_isolated_target(): void
    {
        $values = ['FLOWER_SAMPLE_APPLY' => 'true', 'FLOWER_SAMPLE_DATABASE' => $this->target()];
        $original = [];
        foreach ($values as $key => $value) {
            $original[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            putenv($key.'='.$value);
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
        try {
            $this->seed(FlowerSampleSeeder::class);
            $before = $this->snapshot('products');
            $this->seed(FlowerSampleSeeder::class);
            $this->assertSame(80, Product::count());
            $this->assertSame($before, $this->snapshot('products'));
        } finally {
            foreach ($original as $key => [$process, $environment, $server]) {
                putenv($process === false ? $key : $key.'='.$process);
                if ($environment === null) {
                    unset($_ENV[$key]);
                } else {
                    $_ENV[$key] = $environment;
                }
                if ($server === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $server;
                }
            }
        }
    }
}
