<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Tổng quan quản trị');
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
}
