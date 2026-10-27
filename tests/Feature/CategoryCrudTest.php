<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị viên']);
        $userRole = Role::create(['name' => 'User', 'description' => 'Khách hàng']);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole->id);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole->id);
    }

    public function test_admin_can_view_category_list(): void
    {
        Category::create(['name' => 'Hoa Khai Trương', 'slug' => 'hoa-khai-truong']);

        $response = $this->actingAs($this->admin)->get('/admin/categories');
        $response->assertStatus(200);
        $response->assertSee('Hoa Khai Trương');
    }

    public function test_admin_can_view_create_category_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/create');
        $response->assertStatus(200);
        $response->assertSee('Thêm danh mục mới');
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Hoa Lan Hồ Điệp',
            'slug' => 'hoa-lan-ho-diep',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['slug' => 'hoa-lan-ho-diep']);
    }

    public function test_admin_can_create_category_with_auto_generated_slug(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Hoa Hồng Nhập Khẩu',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['slug' => 'hoa-hong-nhap-khau']);
    }

    public function test_category_validation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_view_edit_category_page(): void
    {
        $category = Category::create(['name' => 'Hoa Sinh Nhật', 'slug' => 'hoa-sinh-nhat']);

        $response = $this->actingAs($this->admin)->get("/admin/categories/{$category->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Hoa Sinh Nhật');
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create(['name' => 'Hoa Đám Tang', 'slug' => 'hoa-dam-tang']);

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Hoa Chia Buồn',
            'slug' => 'hoa-chia-buon',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Hoa Chia Buồn', 'slug' => 'hoa-chia-buon']);
        $this->assertDatabaseMissing('categories', ['slug' => 'hoa-dam-tang']);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::create(['name' => 'Hoa Tạm', 'slug' => 'hoa-tam']);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");
        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_non_admin_cannot_access_categories(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/categories');
        $response->assertStatus(403);
    }
}
