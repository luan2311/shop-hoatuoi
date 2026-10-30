<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class ReturnUrlSecurityTest extends TestCase
{
    use UsesMongoDatabase;

    public static function unsafePaths(): array
    {
        return array_map(fn ($path) => [$path], [
            'https://example.invalid/x', '//example.invalid/x', '/\\example.invalid',
            '%2f%2fexample.invalid', '/%2fexample.invalid', '/%5cexample.invalid',
            '/%255cexample.invalid', '/%252fexample.invalid', '/%0d%0aLocation:evil',
            ['bad'], 'javascript:alert(1)',
        ]);
    }

    #[DataProvider('unsafePaths')]
    public function test_unsafe_return_url_falls_back_to_home(mixed $path): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'returnUrl' => $path])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_local_return_path_preserves_query(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'returnUrl' => '/products?q=hoa'])
            ->assertRedirect('/products?q=hoa');
    }

    public function test_unsafe_session_intended_url_is_not_used(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => '//example.invalid'])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect('/');
    }

    public function test_valid_session_intended_path_is_used(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => '/cart'])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect('/cart');
    }

    public function test_same_origin_absolute_intended_url_keeps_local_destination(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => url('/cart')])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect('/cart');
    }
}
