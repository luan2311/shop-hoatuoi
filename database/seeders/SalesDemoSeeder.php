<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('SalesDemoSeeder only runs in local/testing environments.');
        }
        $passwords = [
            'User' => env('SALES_DEMO_USER_PASSWORD'),
            'Admin' => env('SALES_DEMO_ADMIN_PASSWORD'),
        ];
        foreach ($passwords as $password) {
            if (! is_string($password) || strlen($password) < 12) {
                throw new RuntimeException('Set both SALES_DEMO_*_PASSWORD values (at least 12 characters).');
            }
        }
        foreach ($passwords as $role => $password) {
            // Never change credentials or elevate an account already in the database.
            User::firstOrCreate(['email' => strtolower($role).'.sales@example.test'], [
                'username' => 'sales_'.strtolower($role),
                'name' => 'Sales Demo '.$role,
                'password' => Hash::make($password),
                'roles' => [$role],
                'is_active' => true,
            ]);
        }
        Product::firstOrCreate(['code' => 'TIEN-DEMO-01'], [
            'name' => 'Giỏ hoa kiểm thử bán hàng',
            'slug' => 'gio-hoa-kiem-thu-tien',
            'price' => 350000,
            'stock' => 20,
            'status' => 'Còn hàng',
            'image' => '',
            'description' => 'Dữ liệu tối thiểu để kiểm thử giỏ hàng và đơn hàng.',
            'category' => ['code' => 'TIEN-DEMO', 'name' => 'Dữ liệu kiểm thử', 'slug' => 'du-lieu-kiem-thu'],
        ]);
    }
}
