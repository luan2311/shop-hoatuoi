<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

abstract class ProductTestCase extends TestCase
{
    use UsesMongoDatabase;

    protected function actor(bool $admin = true): User
    {
        return User::factory()->create(['roles' => [$admin ? 'Admin' : 'User']]);
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
