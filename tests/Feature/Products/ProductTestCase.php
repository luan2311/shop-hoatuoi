<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class ProductTestCase extends TestCase
{
    private ?string $testDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('AN_MONGODB_TESTS') !== '1') {
            $this->markTestSkipped('Run php artisan test --configuration=phpunit.products.xml with the isolated MongoDB server.');
        }
        if (! extension_loaded('mongodb')) {
            $this->fail('MongoDB extension required for integration tests.');
        }
        $this->assertSame('mongodb://127.0.0.1:27028', config('database.connections.mongodb.dsn'));
        $this->testDatabase = 'shop_hoatuoi_an_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb.database' => $this->testDatabase]);
        DB::purge('mongodb');
        DB::connection('mongodb')->getDatabase()->command(['ping' => 1]);
        $migration = require database_path('migrations/mongodb/2026_10_26_000001_create_product_catalog.php');
        $migration->up();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testDatabase !== null && $this->app) {
                $connection = DB::connection('mongodb');
                if ($connection->getDatabaseName() !== $this->testDatabase || ! preg_match('/^shop_hoatuoi_an_test_[a-f0-9]{16}$/', $this->testDatabase)) {
                    throw new \LogicException('Unsafe test cleanup refused.');
                }
                $connection->getDatabase()->drop();
                DB::purge('mongodb');
            }
        } finally {
            parent::tearDown();
        }
    }

    protected function actor(bool $admin = true): User
    {
        $actor = new class extends User
        {
            public bool $admin = false;

            public function hasRole(string $role): bool
            {
                return $this->admin && $role === 'Admin';
            }
        };
        $actor->forceFill(['id' => 1, 'name' => 'An Test', 'email' => 'an@example.test']);
        $actor->admin = $admin;

        return $actor;
    }

    protected function category(array $attributes = []): Category
    {
        return Category::create(array_replace(['code' => 'LH001', 'name' => 'Hoa Hồng', 'slug' => 'hoa-hong'], $attributes));
    }

    protected function product(Category $category, array $attributes = []): Product
    {
        return Product::create(array_replace([
            'code' => 'SP001', 'name' => 'Bó Hoa Ecuador', 'price' => 500000, 'stock' => 10,
            'status' => 'Còn hàng', 'description' => 'Mô tả hoa tươi', 'brand' => 'Demo',
            'category_id' => (string) $category->id, 'category' => $category->only(['code', 'name', 'slug']),
        ], $attributes));
    }
}
