<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

class ProductAuthIntegrationTest extends ProductTestCase
{
    public function test_mongodb_auth_and_group_seeder_integrate_with_catalog(): void
    {
        $this->assertSame('mongodb', config('database.default'));
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(5, Category::count());
        $this->assertSame(7, Product::count());
        $product = Product::where('code', 'PVN297')->firstOrFail();
        $this->assertSame('LH001', $product->category['code']);
        $admin = User::where('email', 'admin@shophoatuoi.com')->firstOrFail();
        $this->assertTrue(Hash::check('Admin@123', $admin->password));
        $this->post('/login', ['email' => $admin->email, 'password' => 'Admin@123'])->assertRedirect('/admin/dashboard');
        $this->get('/admin/products')->assertOk()->assertSee('PVN297');
        $this->put('/admin/products/'.$product->id, [
            'code' => $product->code, 'name' => $product->name,
            'category_id' => $product->category_id, 'price' => 600000,
            'stock' => 9, 'status' => 'Còn hàng',
        ])->assertRedirect('/admin/products');
        $this->assertSame(600000, $product->fresh()->price);
        $this->post('/logout')->assertRedirect('/');
        $this->get('/admin/products')->assertRedirect('/login');
        $this->post('/login', ['email' => 'user@shophoatuoi.com', 'password' => 'User@123'])->assertRedirect('/admin/products');
        $this->get('/admin/products')->assertForbidden();
    }
}
