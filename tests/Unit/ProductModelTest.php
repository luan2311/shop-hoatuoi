<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    public function test_image_url_resolves_external_urls(): void
    {
        $product = new Product(['image' => 'https://example.com/flower.jpg']);
        $this->assertSame('https://example.com/flower.jpg', $product->image_url);

        $productHttp = new Product(['image' => 'http://example.com/flower.jpg']);
        $this->assertSame('http://example.com/flower.jpg', $productHttp->image_url);
    }

    public function test_image_url_resolves_products_prefix(): void
    {
        $product = new Product(['image' => 'products/sample.jpg']);
        $this->assertStringEndsWith('/storage/products/sample.jpg', $product->image_url);
    }

    public function test_image_url_resolves_raw_filename(): void
    {
        $product = new Product(['image' => 'kt2a5767-1748932933010.jpg']);
        $this->assertStringEndsWith('/images/kt2a5767-1748932933010.jpg', $product->image_url);
    }

    public function test_image_url_returns_null_when_empty(): void
    {
        $product = new Product(['image' => '']);
        $this->assertNull($product->image_url);

        $productNull = new Product(['image' => null]);
        $this->assertNull($productNull->image_url);
    }

    public function test_category_attribute_returns_embedded_array(): void
    {
        $product = new Product([
            'category' => [
                'code' => 'LH001',
                'name' => 'Bó Hoa Tươi',
                'slug' => 'bo-hoa-tuoi',
            ],
        ]);

        $this->assertIsArray($product->category);
        $this->assertSame('Bó Hoa Tươi', $product->category['name']);
        $this->assertSame('LH001', $product->category['code']);
        $this->assertSame('bo-hoa-tuoi', $product->category['slug']);
        $this->assertSame('Bó Hoa Tươi', data_get($product->category, 'name'));
    }

    public function test_category_record_relation_is_defined(): void
    {
        $product = new Product;
        $relation = $product->categoryRecord();
        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    public function test_resolve_item_image_url_resolves_image_and_fallback(): void
    {
        $this->assertStringEndsWith('/images/kt2a5767-1748932933010.jpg', Product::resolveItemImageUrl('kt2a5767-1748932933010.jpg'));
        $this->assertNull(Product::resolveItemImageUrl(null, 'NONEXISTENT'));
    }
}
