<?php

namespace Tests\Feature\Products;

class ProductSearchAjaxTest extends ProductTestCase
{
    public function test_search_matches_name_description_category_with_six_result_limit(): void
    {
        $category = $this->category();
        for ($i = 1; $i <= 8; $i++) {
            $this->product($category, ['code' => 'SP'.$i]);
        }
        foreach (['ecuador', 'Mô tả', 'Hoa Hồng'] as $term) {
            $this->getJson('/api/products/search?q='.urlencode($term))->assertOk()
                ->assertJsonCount(6, 'products')->assertJsonStructure(['products' => [['id', 'name', 'price', 'formatted_price', 'slug', 'image_url', 'url']]]);
        }
        $this->getJson('/api/products/search?q=unknown')->assertExactJson(['products' => []]);
        $this->getJson('/api/products/search?q=')->assertExactJson(['products' => []]);
    }

    public function test_search_treats_regex_and_wildcards_as_literal_and_validates_input(): void
    {
        $this->product($this->category(), ['name' => 'Hoa [đặc biệt] 100%']);
        $this->getJson('/api/products/search?q='.urlencode('[đặc biệt]'))->assertOk()->assertJsonCount(1, 'products');
        $this->getJson('/api/products/search?q=.*')->assertOk()->assertJsonCount(0, 'products');
        $this->getJson('/api/products/search?q=%25')->assertOk()->assertJsonCount(1, 'products');
        $this->getJson('/api/products/search?q[]=invalid')->assertUnprocessable();
        $this->getJson('/api/products/search?q='.str_repeat('a', 101))->assertUnprocessable();
    }

    public function test_catalog_search_is_grouped_with_category_filter_and_accepts_zero(): void
    {
        $first = $this->category();
        $second = $this->category(['code' => 'LH002', 'slug' => 'other', 'name' => 'Other']);
        $this->product($first, ['name' => 'Hoa 0']);
        $this->product($first, ['code' => 'SP2', 'name' => 'Tulip']);
        $this->product($second, ['code' => 'SP3', 'name' => 'Hoa 0']);
        $response = $this->get('/products?category_slug=hoa-hong&q=0')->assertOk();
        $this->assertSame(1, $response->viewData('products')->total());
        $this->actingAs($this->actor())->get('/admin/products?q=0')->assertOk()->assertSee('Hoa 0')->assertDontSee('Tulip');
    }
}
