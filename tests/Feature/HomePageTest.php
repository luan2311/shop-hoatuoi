<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_homepage_loads_successfully_with_header_and_banners(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Shop Hoa Tươi');
    }

    public function test_homepage_shows_products_categories_and_news_from_mongodb(): void
    {
        Category::create(['code' => 'LH001', 'name' => 'Bó Hoa Tươi', 'slug' => 'bo-hoa-tuoi']);
        Product::create([
            'code' => 'SP1', 'name' => 'Bó Hồng Đỏ Ecuador', 'price' => 700000, 'stock' => 5,
            'status' => 'Còn hàng', 'image' => 'hong-do.jpg',
            'category' => ['code' => 'LH001', 'name' => 'Bó Hoa Tươi', 'slug' => 'bo-hoa-tuoi'],
        ]);
        Post::create(['title' => 'Cách bảo quản hoa tươi', 'published_at' => now()]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Bó Hồng Đỏ Ecuador')
            ->assertSee('700.000 đ')
            ->assertSee('/products?category_slug=bo-hoa-tuoi', false)
            ->assertSee('Cách bảo quản hoa tươi');
    }
}
