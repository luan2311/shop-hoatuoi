<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_admin_can_view_dashboard(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $category = Category::create(['code' => 'LH001', 'name' => 'Hoa Tươi', 'slug' => 'hoa-tuoi']);
        Product::create([
            'code' => 'SP001',
            'name' => 'Hoa Hồng',
            'price' => 100000,
            'stock' => 10,
            'category_id' => $category->id,
            'category' => ['code' => 'LH001', 'name' => 'Hoa Tươi', 'slug' => 'hoa-tuoi'],
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Đơn hàng gần đây');
    }

    public function test_admin_dashboard_displays_statistic_counts(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        Category::create(['name' => 'Hoa Tươi', 'slug' => 'hoa-tuoi']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas(['totalCategories', 'totalProducts', 'totalOrders', 'totalUsers']);
    }

    public function test_admin_can_fetch_revenue_chart_data(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get('/admin/dashboard/revenue-data');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['date', 'revenue'],
            ],
        ]);
    }

    public function test_non_admin_cannot_fetch_revenue_chart_data(): void
    {
        $userRole = Role::create(['name' => 'User', 'description' => 'Khách hàng']);
        $user = User::factory()->create();
        $user->roles()->attach($userRole->id);

        $response = $this->actingAs($user)->get('/admin/dashboard/revenue-data');
        $response->assertStatus(403);
    }
}
