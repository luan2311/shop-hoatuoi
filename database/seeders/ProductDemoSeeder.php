<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Demo fixture only. Existing categories/products are never overwritten.
        $category = Category::where('slug', 'bo-hoa-tuoi')->first();
        if (! $category) {
            $category = Category::firstOrCreate(['code' => 'LH001'], ['name' => 'Bó Hoa Tươi', 'slug' => 'bo-hoa-tuoi']);
        }
        if (! $category->code || ! $category->slug) {
            throw new \LogicException('Danh mục demo phải có code và slug hợp lệ.');
        }
        for ($index = 1; $index <= 15; $index++) {
            $code = sprintf('ANDEMO%02d', $index);
            Product::firstOrCreate(['code' => $code], [
                'name' => "Bó hoa demo An {$index}", 'slug' => 'bo-hoa-demo-an-'.$index,
                'price' => 200000 + $index * 10000, 'stock' => 10,
                'status' => 'Còn hàng', 'brand' => 'Demo', 'description' => 'Dữ liệu demo module sản phẩm.',
                'category_id' => (string) $category->id, 'category' => $category->only(['code', 'name', 'slug']),
            ]);
        }
    }
}
