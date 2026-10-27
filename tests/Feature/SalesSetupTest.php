<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\SalesDemoSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class SalesSetupTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_migrations_and_demo_seeder_are_repeatable_and_keep_existing_data(): void
    {
        // Random runtime-only passwords, no committed credentials.
        $password = bin2hex(random_bytes(16));
        foreach (['SALES_DEMO_USER_PASSWORD', 'SALES_DEMO_ADMIN_PASSWORD'] as $key) {
            putenv($key.'='.$password);
            $_ENV[$key] = $password;
            $_SERVER[$key] = $password;
        }
        try {
            $this->seed(SalesDemoSeeder::class);
            $user = User::where('email', 'user.sales@example.test')->firstOrFail();
            $admin = User::where('email', 'admin.sales@example.test')->firstOrFail();
            $this->assertTrue(Hash::check($password, $user->password));
            $this->assertTrue($admin->hasRole('Admin'));
            $product = Product::where('code', 'TIEN-DEMO-01')->firstOrFail();
            $product->update(['stock' => 7]);
            $this->seed(SalesDemoSeeder::class);
            $this->assertSame(2, User::count());
            $this->assertSame(1, Product::count());
            $this->assertSame(7, $product->fresh()->stock);
            $this->assertSame($user->password, $user->fresh()->password);
            $this->artisan('migrate', ['--path' => 'database/migrations/mongodb', '--force' => true])->assertExitCode(0);
            $migration = require database_path('migrations/mongodb/2026_10_26_000001_create_sales_indexes.php');
            $migration->up();
            $this->assertSame(1, Product::count());
        } finally {
            foreach (['SALES_DEMO_USER_PASSWORD', 'SALES_DEMO_ADMIN_PASSWORD'] as $key) {
                putenv($key);
                unset($_ENV[$key]);
                unset($_SERVER[$key]);
            }
        }
    }
}
