<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(app()->environment('testing'));
        $name = 'shop_hoatuoi_test_pagination_'.bin2hex(random_bytes(8));
        config(['database.default' => 'mongodb', 'database.connections.mongodb.database' => $name]);
        DB::purge('mongodb');
        $this->assertNotContains($name, iterator_to_array(DB::connection('mongodb')->getMongoClient()->listDatabaseNames()));
        $admin = User::factory()->create();
        $admin->forceFill(['roles' => ['Admin']])->save();
        $this->actingAs($admin)->withoutVite();
    }

    public static function lists(): array
    {
        return [
            ['products', 'q', ['sort' => 'alpha-asc']],
            ['categories', 'search', ['sort' => 'count-desc', 'usage' => 'empty']],
            ['orders', 'searchString', []],
            ['users', 'searchString', []],
            ['posts', 'searchString', []],
            ['contacts', 'searchString', []],
        ];
    }

    #[DataProvider('lists')]
    public function test_sizes_pages_and_filter_preservation(string $list, string $search, array $filters): void
    {
        $docs = [];
        for ($i = 0; $i < 25; $i++) {
            $docs[] = [
                'name' => 'Pagination QA '.$i, 'title' => 'Pagination QA '.$i,
                'full_name' => 'Pagination QA '.$i, 'customer' => ['name' => 'Pagination QA '.$i],
                'username' => 'pagination'.$i, 'email' => 'pagination'.$i.'@example.test',
                'code' => 'PAG'.$i, 'slug' => 'pagination-'.$i, 'order_code' => 'PAG'.$i,
                'stock' => 1, 'price' => 1000, 'total' => 1000, 'message' => 'QA',
                'roles' => ['User'], 'status' => 'Pending',
            ];
        }
        DB::connection('mongodb')->getDatabase()->selectCollection($list)->insertMany($docs);
        $query = [$search => 'Pagination QA'] + $filters + ['per_page' => 20];
        $url = '/admin/'.$list.'?'.http_build_query($query);
        $response = $this->get($url)->assertOk()->assertSee('data-admin-pagination', false);
        $page = $response->viewData($list);
        $this->assertSame(20, $page->perPage());
        $this->assertSame(20, $page->count());
        $this->assertSame(25, $page->total());
        parse_str(parse_url($page->nextPageUrl(), PHP_URL_QUERY), $next);
        $this->assertEquals($query + ['page' => 2], $next);
        $second = $this->get($url.'&page=2')->assertOk()->viewData($list);
        $this->assertSame(5, $second->count());
        $this->assertSame(21, $second->firstItem());
        $this->assertSame(25, $second->lastItem());
        if ($list === 'categories') {
            $normal = $this->get('/admin/categories?per_page=20&sort=alpha-asc')->assertOk()->viewData('categories');
            $this->assertSame(20, $normal->count());
            $this->assertSame(20, $normal->perPage());
        }
        foreach ([5, 10, 50, 100] as $size) {
            $page = $this->get('/admin/'.$list.'?'.http_build_query(array_replace($query, ['per_page' => $size])))
                ->assertOk()->viewData($list);
            $this->assertSame($size, $page->perPage());
            $this->assertSame(min($size, 25), $page->count());
        }
    }

    public function test_invalid_sizes_and_page_are_rejected_on_every_list(): void
    {
        foreach (self::lists() as [$list]) {
            foreach (['per_page=10000', 'per_page[]=20', 'per_page=-1', 'page=0'] as $query) {
                $this->getJson('/admin/'.$list.'?'.$query)->assertUnprocessable();
            }
        }
    }

    public function test_empty_list_still_has_summary_and_size_selector(): void
    {
        $this->get('/admin/contacts')->assertOk()->assertSee('0 – 0')->assertSee('name="per_page"', false);
    }
}
