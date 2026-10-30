<?php

namespace Tests\Feature\Products;

class FrontendProductTest extends ProductTestCase
{
    public function test_guest_catalog_paginates_twelve_and_preserves_filters_and_sort(): void
    {
        $category = $this->category();
        for ($i = 1; $i <= 15; $i++) {
            $this->product($category, ['code' => sprintf('SP%03d', $i), 'price' => $i * 1000]);
        }
        $other = $this->category(['code' => 'LH002', 'name' => 'Tulip', 'slug' => 'tulip']);
        $this->product($other, ['code' => 'OTHER']);
        $url = '/products?category_slug=hoa-hong&sort=price-desc';
        $response = $this->get($url)->assertOk();
        $products = $response->viewData('products');
        $this->assertCount(12, $products);
        $this->assertSame(15, $products->total());
        $this->assertSame(15000.0, $products->first()->price);
        $this->assertStringContainsString('category_slug=hoa-hong', $products->nextPageUrl());
        $this->assertStringContainsString('sort=price-desc', $products->nextPageUrl());
        $this->assertCount(3, $this->get($url.'&page=2')->assertOk()->viewData('products'));
        $ascending = $this->get('/products?category_id='.$category->id.'&sort=price-asc')->assertOk();
        $this->assertSame(1000.0, $ascending->viewData('products')->first()->price);
    }

    public function test_detail_reads_legacy_snapshot_and_limits_related_to_four(): void
    {
        $category = $this->category();
        $product = $this->product($category, ['description' => '<script>alert(1)</script><p>Hoa tươi</p>']);
        $product->unset('category_id');
        for ($i = 2; $i <= 7; $i++) {
            $this->product($category, ['code' => 'SP'.$i]);
        }
        $response = $this->get('/products/'.$product->slug)->assertOk()->assertSee('Hoa tươi')->assertDontSee('<script>alert(1)</script>', false);
        $related = $response->viewData('relatedProducts');
        $this->assertCount(4, $related);
        $this->assertFalse($related->contains('id', $product->id));
        $this->get('/products/does-not-exist')->assertNotFound();
        $this->get('/products?category_id='.str_repeat('a', 24))->assertNotFound();
        $this->get('/products?category_slug=missing')->assertOk()->assertSee('Không tìm thấy sản phẩm.');
    }

    public function test_invalid_browse_inputs_are_rejected(): void
    {
        foreach (['page=0', 'page[]=1', 'sort=arbitrary', 'q[]=bad', 'category_id=invalid'] as $query) {
            $this->getJson('/products?'.$query)->assertUnprocessable();
        }
    }
}
