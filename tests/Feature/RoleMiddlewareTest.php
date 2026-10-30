<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_user_without_admin_role_cannot_access_admin_dashboard(): void
    {
        $userRole = Role::create(['name' => 'User', 'description' => 'Khách hàng']);
        $user = User::factory()->create();
        $user->roles()->attach($userRole->id);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_user_can_pass_role_middleware(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị viên']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }
}
