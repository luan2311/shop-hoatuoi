<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_shopflower_csharp_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'TenDangNhap' => $user->email,
            'MatKhau' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $adminRole = \App\Models\Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_admin_is_redirected_to_admin_dashboard_even_with_session_intended_url(): void
    {
        $adminRole = \App\Models\Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->withSession(['url.intended' => '/cart'])->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
