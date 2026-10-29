<?php

namespace Tests\Feature\Auth;

use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_register_using_shopflower_csharp_fields(): void
    {
        $response = $this->post('/register', [
            'TenDangNhap' => 'flowerlover',
            'Email' => 'flowerlover@example.com',
            'TenHienThi' => 'Người Yêu Hoa',
            'MatKhau' => 'password123',
            'XacNhanMatKhau' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }
}
