<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Database\Seeders\ProductDemoSeeder;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\BulkWriteException;

class ProductSchemaTest extends ProductTestCase
{
    public function test_migration_and_seeder_are_repeatable_without_overwriting_existing_products(): void
    {
        $migration = require database_path('migrations/mongodb/2026_10_26_000001_create_product_catalog.php');
        $migration->up();
        $this->seed(ProductDemoSeeder::class);
        $product = Product::where('code', 'ANDEMO01')->firstOrFail();
        $product->update(['price' => 1]);
        $this->seed(ProductDemoSeeder::class);
        $this->assertSame(15, Product::count());
        $this->assertSame(1, $product->fresh()->price);
        $raw = DB::connection('mongodb')->getDatabase()->products->findOne(['code' => 'ANDEMO01']);
        $this->assertInstanceOf(ObjectId::class, $raw['_id']);
        $this->assertInstanceOf(UTCDateTime::class, $raw['created_at']);
        $this->assertIsInt($raw['stock']);
        $this->assertTrue(is_float($raw['price']) || is_int($raw['price']));
        $this->assertSame('LH001', $raw['category']['code']);
    }

    public function test_database_unique_index_rejects_duplicate_even_without_http_validation(): void
    {
        $category = $this->category();
        $this->product($category);
        $this->expectException(BulkWriteException::class);
        $this->product($category, ['slug' => 'different-slug']);
    }
}
