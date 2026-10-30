<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
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
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_authenticate_using_shopflower_csharp_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'TenDangNhap' => $user->email,
            'MatKhau' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_user_with_role_user_is_redirected_to_home_after_login(): void
    {
        $user = User::factory()->create([
            'username' => 'user1',
            'roles' => ['User'],
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_dashboard_url_redirects_role_user_to_home(): void
    {
        $user = User::factory()->create([
            'username' => 'user1',
            'roles' => ['User'],
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_dashboard_url_redirects_admin_to_admin_dashboard(): void
    {
        $adminRole = Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $adminRole = Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_user_demo_is_redirected_to_home_after_login_with_username(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userDemo = User::factory()->create([
            'username' => 'user_demo',
            'email' => 'user@shophoatuoi.com',
            'password' => 'password',
        ]);
        $userDemo->roles()->attach($userRole->id);

        $response = $this->post('/login', [
            'email' => 'user_demo',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_user_demo_is_redirected_to_home_after_login_with_email(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userDemo = User::factory()->create([
            'username' => 'user_demo',
            'email' => 'user@shophoatuoi.com',
            'password' => 'password',
        ]);
        $userDemo->roles()->attach($userRole->id);

        $response = $this->post('/login', [
            'email' => 'user@shophoatuoi.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_user_demo_is_redirected_to_home_using_shopflower_csharp_fields(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userDemo = User::factory()->create([
            'username' => 'user_demo',
            'email' => 'user@shophoatuoi.com',
            'password' => 'password',
        ]);
        $userDemo->roles()->attach($userRole->id);

        $response = $this->post('/login', [
            'TenDangNhap' => 'user_demo',
            'MatKhau' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_user_demo_is_redirected_to_home_even_with_session_intended_dashboard(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userDemo = User::factory()->create([
            'username' => 'user_demo',
            'email' => 'user@shophoatuoi.com',
            'password' => 'password',
        ]);
        $userDemo->roles()->attach($userRole->id);

        $response = $this->withSession(['url.intended' => '/dashboard'])->post('/login', [
            'email' => 'user_demo',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_admin_is_redirected_to_admin_dashboard_even_with_session_intended_url(): void
    {
        $adminRole = Role::create(['name' => 'Admin']);
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

    public function test_deactivated_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_deactivated_user_cannot_authenticate_by_username(): void
    {
        $user = User::factory()->create([
            'username' => 'inactive_user',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive_user',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_already_authenticated_deactivated_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }
}
