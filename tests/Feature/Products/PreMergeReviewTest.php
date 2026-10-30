<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\CommandException;

class PreMergeReviewTest extends ProductTestCase
{
    public function test_group_seeder_preserves_existing_renamed_catalog(): void
    {
        $category = $this->category(['slug' => 'renamed']);
        $product = $this->product($category, ['code' => 'PVN297', 'price' => 123]);
        $before = $product->fresh()->getAttributes();
        $this->seed(DatabaseSeeder::class);
        $this->assertEquals($before, $product->fresh()->getAttributes());
        $this->assertSame('renamed', $category->fresh()->slug);
        $this->assertSame(7, Product::count());
    }

    public function test_migration_rejects_duplicates_without_rewriting_documents(): void
    {
        $this->product($this->category());
        $collection = DB::connection('mongodb')->getDatabase()->products;
        $collection->dropIndex('code_1');
        $duplicate = (array) $collection->findOne();
        unset($duplicate['_id']);
        $duplicate['slug'] = 'duplicate-code';
        $collection->insertOne($duplicate);
        $before = iterator_to_array($collection->find());
        $migration = require database_path('migrations/mongodb/2026_10_26_000001_create_product_catalog.php');
        try {
            $migration->up();
            $this->fail('Duplicate data must block unique index creation.');
        } catch (CommandException $exception) {
            $this->assertSame(11000, $exception->getCode());
        }
        $this->assertEquals($before, iterator_to_array($collection->find()));
    }

    public function test_concurrent_image_change_blocks_stale_delete(): void
    {
        Storage::fake('public');
        $product = $this->product($this->category(), ['image' => 'products/original.jpg']);
        Storage::disk('public')->put('products/concurrent.jpg', 'concurrent');
        Product::retrieved(function (Product $loaded) {
            DB::connection('mongodb')->getDatabase()->products->updateOne(
                ['_id' => new ObjectId($loaded->id)],
                ['$set' => ['image' => 'products/concurrent.jpg']],
            );
        });
        $this->actingAs($this->actor())->deleteJson('/admin/products/'.$product->id)->assertStatus(409);
        $this->assertSame(1, Product::count());
        Storage::disk('public')->assertExists('products/concurrent.jpg');
    }

    public function test_invalid_array_input_can_be_rendered_again_without_a_500(): void
    {
        $this->actingAs($this->actor());
        $this->from('/admin/products/create')->post('/admin/products', ['name' => ['bad'], 'code' => ['bad'], 'description' => ['bad']])
            ->assertSessionHasErrors(['name', 'code', 'description'])->assertRedirect('/admin/products/create');
        $this->get('/admin/products/create')->assertOk();
    }

    public function test_reassigning_category_changes_both_reference_and_snapshot(): void
    {
        $old = $this->category();
        $new = $this->category(['code' => 'LH002', 'slug' => 'tulip', 'name' => 'Tulip']);
        $product = $this->product($old);
        $this->actingAs($this->actor())->put('/admin/products/'.$product->id, [
            'code' => $product->code, 'name' => $product->name, 'price' => 1, 'stock' => 1,
            'status' => 'Còn hàng', 'category_id' => $new->id,
        ])->assertRedirect();
        $fresh = $product->fresh();
        $this->assertSame($new->id, $fresh->categoryRecord->id);
        $this->assertSame($new->only(['code', 'name', 'slug']), $fresh->category);
        $this->assertSame(0, $this->get('/products?category_slug=hoa-hong')->viewData('products')->total());
        $this->assertSame(1, $this->get('/products?category_slug=tulip')->viewData('products')->total());
    }

    public function test_filter_by_current_category_slug_finds_old_snapshots_after_category_rename(): void
    {
        $category = $this->category();
        $this->product($category);
        $category->update(['slug' => 'hoa-hong-moi']);
        $response = $this->get('/products?category_slug=hoa-hong-moi')->assertOk();
        $this->assertSame(1, $response->viewData('products')->total());
    }

    public function test_migration_preserves_existing_documents_validator_and_equivalent_named_index(): void
    {
        $product = $this->product($this->category());
        $collection = DB::connection('mongodb')->getDatabase()->products;
        $before = $collection->findOne(['code' => $product->code]);
        // Only this randomly named test database is touched.
        $collection->dropIndex('code_1');
        $collection->createIndex(['code' => 1], ['unique' => true, 'name' => 'team_products_code_unique']);
        $database = DB::connection('mongodb')->getDatabase();
        $options = iterator_to_array($database->listCollections(['filter' => ['name' => 'products']]))[0]->getOptions();
        $migration = require database_path('migrations/mongodb/2026_10_26_000001_create_product_catalog.php');
        $migration->up();
        $this->assertEquals($before, $collection->findOne(['code' => $product->code]));
        $this->assertEquals($options, iterator_to_array($database->listCollections(['filter' => ['name' => 'products']]))[0]->getOptions());
    }

    public function test_concurrent_image_replacement_is_not_overwritten_or_left_orphaned(): void
    {
        Storage::fake('public');
        $category = $this->category();
        $product = $this->product($category, ['image' => 'products/original.jpg']);
        Storage::disk('public')->put('products/original.jpg', 'original');
        Storage::disk('public')->put('products/concurrent.jpg', 'concurrent');
        $interleaved = false;
        Product::retrieved(function (Product $loaded) use (&$interleaved) {
            if (! $interleaved && $loaded->code === 'SP001') {
                $interleaved = true;
                DB::connection('mongodb')->getDatabase()->products->updateOne(
                    ['_id' => new ObjectId($loaded->id)],
                    ['$set' => ['image' => 'products/concurrent.jpg']],
                );
                Storage::disk('public')->delete('products/original.jpg');
            }
        });
        $this->actingAs($this->actor())->putJson('/admin/products/'.$product->id, [
            'code' => 'SP001', 'name' => 'Stale update', 'price' => 2, 'stock' => 2,
            'status' => 'Còn hàng', 'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('replacement.png'),
        ])->assertStatus(409);
        $this->assertSame('products/concurrent.jpg', $product->fresh()->image);
        $this->assertSame(['products/concurrent.jpg'], Storage::disk('public')->allFiles());
    }
}
