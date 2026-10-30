<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductLifecycle;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminProductBrowseTest extends TestCase
{
    private Category $category;

    public function test_product_forms_share_one_confirm_component_and_fail_closed_until_js_initializes(): void
    {
        $product = Product::firstOrFail();
        foreach ([['/admin/products/create', 'success'], [route('admin.products.edit', $product), 'primary'], ['/admin/products', 'danger']] as [$url, $type]) {
            $response = $this->get($url)->assertOk();
            $response->assertSee('data-confirm-form', false)->assertSee('data-confirm-gate disabled', false);
            $response->assertSee('data-confirm-transport="fetch"', false)->assertSee('data-confirm-type="'.$type.'"', false);
            $response->assertSee('name="_token"', false)->assertDontSee('onsubmit="return confirm(', false);
            $this->assertSame(1, substr_count($response->getContent(), 'id="adminConfirmModal"'));
        }
        $this->get(route('admin.products.edit', $product))->assertSee('name="_method" value="PUT"', false);
        $this->get('/admin/products')->assertSee('name="_method" value="DELETE"', false);
        $this->get('/admin/dashboard')->assertDontSee('admin-confirm-modal.js')->assertDontSee('id="adminConfirmModal"', false);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(app()->environment('testing'));
        $this->assertStringStartsWith('mongodb://127.0.0.1:', config('database.connections.mongodb.dsn'));
        $name = 'shop_hoatuoi_test_admin_ui_'.bin2hex(random_bytes(10));
        config(['database.default' => 'mongodb', 'database.connections.mongodb.database' => $name]);
        DB::purge('mongodb');
        $connection = DB::connection('mongodb');
        $this->assertNotContains($name, iterator_to_array($connection->getMongoClient()->listDatabaseNames()));
        // Only fresh test fixtures, no migrations or cleanup/drop of existing databases.
        $connection->getDatabase()->_test_owner->insertOne(['token' => bin2hex(random_bytes(16))]);
        $admin = User::factory()->create();
        $admin->forceFill(['roles' => ['Admin']])->save();
        $this->actingAs($admin)->withoutVite();
        $this->category = Category::create(['code' => 'UIA', 'name' => 'Hoa A', 'slug' => 'hoa-a']);
        $other = Category::create(['code' => 'UIB', 'name' => 'Hoa B', 'slug' => 'hoa-b']);
        for ($i = 0; $i < 14; $i++) {
            $category = $i < 12 ? $this->category : $other;
            $p = Product::create([
                'code' => sprintf('ADM%03d', $i), 'name' => sprintf('Hoa Test %02d', $i),
                'price' => 100000 + $i, 'stock' => in_array($i, [0, 10]) ? 0 : $i,
                'status' => 'Còn hàng', 'category_id' => (string) $category->id,
                'category' => $category->only(['code', 'name', 'slug']),
            ]);
            $p->forceFill(['created_at' => now()->subMinutes(14 - $i)])->save();
            if (in_array($i, [10, 11])) {
                $p->forceFill(['is_selling' => false])->save();
            }
        }
    }

    public function test_stats_are_global_overlapping_and_page_has_ten_records(): void
    {
        $response = $this->get('/admin/products')->assertOk();
        $this->assertSame(['total' => 14, 'selling' => 12, 'stopped' => 2, 'out-of-stock' => 2], $response->viewData('stats'));
        $this->assertCount(10, $response->viewData('products'));
        $this->assertSame('ADM013', $response->viewData('products')->first()->code);
        $response->assertSee('1 – 10')->assertSee('<strong>14</strong>', false)->assertSee('Xem nhanh sản phẩm');
        $filtered = $this->get('/admin/products?q=ADM013')->assertOk();
        $this->assertSame($response->viewData('stats'), $filtered->viewData('stats'));
        $this->assertCount(1, $filtered->viewData('products'));
    }

    public function test_partial_literal_case_insensitive_name_and_code_search(): void
    {
        $this->assertSame(14, $this->get('/admin/products?q=hOA%20tESt')->assertOk()->viewData('products')->total());
        $this->assertSame(4, $this->get('/admin/products?searchString=adm01')->assertOk()->viewData('products')->total());
        $this->assertSame(0, $this->get('/admin/products?q=.*')->assertOk()->viewData('products')->total());
        $this->assertSame(1, $this->get('/admin/products?query=adm013')->assertOk()->viewData('products')->total());
    }

    public function test_category_and_state_filters_can_combine_and_keep_query_in_pagination(): void
    {
        $url = '/admin/products?category_id='.$this->category->id.'&sale_state=selling&q=hoa&sort=stock-desc';
        $response = $this->get($url)->assertOk();
        $this->assertSame(10, $response->viewData('products')->total());
        $this->assertSame(2, $this->get('/admin/products?sale_state=stopped')->assertOk()->viewData('products')->total());
        $this->assertSame(2, $this->get('/admin/products?sale_state=out-of-stock')->assertOk()->viewData('products')->total());
        $this->assertSame(12, $this->get('/admin/products?category_slug=hoa-a')->assertOk()->viewData('products')->total());
        $response = $this->get('/admin/products?q=hoa&category_slug=hoa-a&sort=price-asc')->assertOk();
        $next = $response->viewData('products')->nextPageUrl();
        $this->assertStringContainsString('category_slug=hoa-a', $next);
        $this->assertStringContainsString('sort=price-asc', $next);
        $this->assertStringContainsString('q=hoa', $next);
        $this->assertCount(2, $this->get($next)->assertOk()->viewData('products'));
    }

    public function test_sorts_use_numeric_values_and_allowed_columns_only(): void
    {
        foreach (['price-asc', 'price-desc', 'stock-asc', 'stock-desc', 'alpha-asc', 'alpha-desc'] as $sort) {
            $rows = $this->get('/admin/products?sort='.$sort)->assertOk()->viewData('products');
            $column = str_starts_with($sort, 'price') ? 'price' : (str_starts_with($sort, 'stock') ? 'stock' : 'name');
            $actual = $rows->pluck($column)->all();
            $expected = $actual;
            str_ends_with($sort, 'asc') ? sort($expected) : rsort($expected);
            $this->assertSame($expected, $actual);
        }
        foreach (['sort=secret_field', 'sale_state=unknown', 'category_id=invalid', 'q[]=bad', 'page=0'] as $invalid) {
            $this->getJson('/admin/products?'.$invalid)->assertUnprocessable();
        }
    }

    public function test_empty_state_reset_and_new_assets_are_scoped_to_product_page(): void
    {
        $response = $this->get('/admin/products?q=not-found')->assertOk();
        $response->assertSee('Không tìm thấy sản phẩm phù hợp.')->assertSee('0 – 0')->assertSee('<strong>0</strong>', false);
        $response->assertSee('href="'.route('admin.products.index').'"', false);
        $this->get('/admin/categories')->assertOk()->assertDontSee('admin-products.css')->assertDontSee('admin-products.js');
        $this->get('/admin/dashboard')->assertOk()->assertDontSee('admin-products.css')->assertDontSee('admin-products.js');
    }

    public function test_existing_delete_reason_is_only_rendered_in_accessible_popover(): void
    {
        $p = Product::where('code', 'ADM000')->firstOrFail();
        DB::connection('mongodb')->getDatabase()->orders->insertOne(['status' => 'Cancelled', 'items' => [['product_code' => $p->code]]]);
        $response = $this->get('/admin/products?q=ADM000')->assertOk();
        $response->assertSee('data-toggle="popover"', false)->assertSee('Không thể xóa');
        $this->assertSame(ProductLifecycle::ORDER_MESSAGE, $response->viewData('deleteReasons')[$p->id]);
        $response->assertDontSee('action="'.route('admin.products.destroy', $p).'"', false);
        $this->deleteJson(route('admin.products.destroy', $p))->assertUnprocessable();
        $this->assertNotNull($p->fresh());
    }
}
