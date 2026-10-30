<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

class CategoryAdminUsageTest extends TestCase
{
    private Category $a;

    private Category $b;

    private Category $empty;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(app()->environment('testing'));
        $name = 'shop_hoatuoi_test_category_ui_'.bin2hex(random_bytes(8));
        config(['database.default' => 'mongodb', 'database.connections.mongodb.database' => $name]);
        DB::purge('mongodb');
        $db = DB::connection('mongodb');
        $this->assertNotContains($name, iterator_to_array($db->getMongoClient()->listDatabaseNames()));
        $user = User::factory()->create();
        $user->forceFill(['roles' => ['Admin']])->save();
        $this->actingAs($user)->withoutVite();
        $this->a = Category::create(['code' => 'CATA', 'name' => 'Hoa Alpha', 'slug' => 'hoa-alpha']);
        $this->b = Category::create(['code' => 'CATB', 'name' => 'Hoa Beta', 'slug' => 'hoa-beta']);
        $this->empty = Category::create(['code' => 'CATE', 'name' => 'Hoa Empty', 'slug' => 'hoa-empty']);
        $collection = $db->getDatabase()->products;
        foreach ([
            ['category' => ['code' => 'CATA']],
            ['category_id' => (string) $this->a->id],
            ['category_id' => new ObjectId($this->a->id)],
            ['category_id' => (string) $this->a->id, 'category' => ['code' => 'CATA'], 'is_selling' => false, 'stock' => 0],
            ['category_id' => (string) $this->b->id, 'category' => ['code' => 'CATA']],
            ['category' => ['slug' => 'hoa-beta']],
        ] as $i => $reference) {
            $collection->insertOne($reference + ['code' => 'QA'.$i, 'name' => 'QA', 'stock' => 10]);
        }
    }

    public function test_mixed_references_counts_stats_and_no_double_count(): void
    {
        $response = $this->get('/admin/categories')->assertOk();
        $usage = $response->viewData('usage');
        $this->assertSame(5, $usage['counts'][$this->a->id]);
        $this->assertSame(2, $usage['counts'][$this->b->id]);
        $this->assertSame(0, $usage['counts'][$this->empty->id]);
        $this->assertSame(1, $usage['conflicts']);
        $this->assertSame(['total' => 3, 'used' => 2, 'empty' => 1], $response->viewData('stats'));
        $response->assertSee('Đang sử dụng')->assertSee('Không thể xóa')->assertSee('data-confirm-title="Xác nhận xóa danh mục"', false);
    }

    public function test_used_category_cannot_be_deleted_and_empty_can(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $before = json_encode(iterator_to_array($db->products->find()));
        $this->deleteJson(route('admin.categories.destroy', $this->a))->assertUnprocessable();
        $this->assertNotNull($this->a->fresh());
        $this->deleteJson(route('admin.categories.destroy', $this->empty))->assertOk()->assertJson(['success' => true]);
        $this->assertNull($this->empty->fresh());
        $this->assertSame($before, json_encode(iterator_to_array($db->products->find())));
    }

    public function test_unknown_reference_and_other_dependency_block_empty_delete(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $id = $db->carts->insertOne(['category_id' => $this->empty->id])->getInsertedId();
        $this->deleteJson(route('admin.categories.destroy', $this->empty))->assertUnprocessable();
        $db->carts->deleteOne(['_id' => $id]); // This test's own fixture only.
        $db->products->insertOne(['category_id' => new ObjectId]);
        $this->deleteJson(route('admin.categories.destroy', $this->empty))->assertUnprocessable();
        $this->assertNotNull($this->empty->fresh());
    }

    public function test_search_filter_sort_and_validation(): void
    {
        foreach (['cata', 'ALPHA', 'hoa-alpha'] as $keyword) {
            $this->assertSame(1, $this->get('/admin/categories?search='.$keyword)->assertOk()->viewData('categories')->total());
        }
        $response = $this->get('/admin/categories?usage=used&sort=count-desc')->assertOk();
        $this->assertSame(2, $response->viewData('categories')->total());
        $this->assertSame($this->a->id, $response->viewData('categories')->first()->id);
        $this->assertSame(1, $this->get('/admin/categories?usage=empty')->assertOk()->viewData('categories')->total());
        $this->getJson('/admin/categories?sort=untrusted')->assertUnprocessable();
    }
}
