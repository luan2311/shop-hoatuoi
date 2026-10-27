<?php

namespace Tests\Concerns;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Support\Str;

trait SalesFixtures
{
    private function customer(string $role = 'User'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['roles' => [$role]])->save();

        return $user;
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'code' => 'SP-'.bin2hex(random_bytes(3)),
            'name' => 'Giỏ hoa thử nghiệm',
            'price' => 350000,
            'stock' => 10,
            'status' => 'Còn hàng',
            'category' => ['code' => 'LH001', 'name' => 'Hoa giỏ', 'slug' => 'hoa-gio'],
        ], $attributes));
    }

    private function delivery(array $attributes = []): array
    {
        return array_merge([
            'checkout_token' => (string) Str::uuid(),
            'receiver_name' => 'Nguyễn Văn Tiến',
            'receiver_phone' => '0901234567',
            'receiver_address' => '123 Nguyễn Trãi, TP.HCM',
            'payment_method' => 'COD',
        ], $attributes);
    }

    private function placedOrder(User $user): Order
    {
        $product = $this->product();
        $service = app(SalesService::class);
        $service->change($user, 'add', $product->id, 2);

        return $service->checkout($user, $this->delivery());
    }
}
