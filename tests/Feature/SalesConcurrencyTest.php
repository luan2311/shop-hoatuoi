<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\SalesService;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class SalesConcurrencyTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    private function race(array $jobs): array
    {
        $start = (string) (microtime(true) + 2);
        $processes = [];
        foreach ($jobs as $job) {
            [$action, $user, $product] = $job;
            $command = [
                PHP_BINARY, base_path('tests/Fixtures/sales-worker.php'), $action, $user->id, $product->id, $start,
            ];
            if (isset($job[3])) {
                $command[] = $job[3];
            }
            $process = new Process($command, base_path(), [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => 'mongodb',
                'MONGODB_DATABASE' => config('database.connections.mongodb.database'),
                'MONGODB_URI' => config('database.connections.mongodb.dsn'),
                'XDEBUG_MODE' => 'off',
            ]);
            $process->start();
            $processes[] = $process;
        }
        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
            $results[] = trim($process->getOutput());
        }

        return $results;
    }

    public function test_concurrent_cart_additions_are_not_lost(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->assertSame(['success', 'success'], $this->race([['add', $user, $product], ['add', $user, $product]]));
        $this->assertSame(2, Cart::first()->items[0]['quantity']);
    }

    public function test_simultaneous_checkout_of_same_cart_creates_only_one_order(): void
    {
        $user = $this->customer();
        $product = $this->product();
        app(SalesService::class)->change($user, 'add', $product->id, 2);
        $results = $this->race([['checkout', $user, $product], ['checkout', $user, $product]]);
        sort($results);
        $this->assertSame(['rejected', 'success'], $results);
        $this->assertSame(1, Order::count());
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame([], Cart::first()->items);
    }

    public function test_two_customers_cannot_oversell_the_last_item(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        $product = $this->product(['stock' => 1]);
        foreach ([$first, $second] as $user) {
            app(SalesService::class)->change($user, 'add', $product->id);
        }
        $results = $this->race([['checkout', $first, $product], ['checkout', $second, $product]]);
        sort($results);
        $this->assertSame(['rejected', 'success'], $results);
        $this->assertSame(1, Order::count());
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame(1, Cart::where('items', '!=', [])->count());
    }

    public function test_concurrent_retries_with_same_token_return_the_same_order(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $token = (string) Str::uuid();
        app(SalesService::class)->change($user, 'add', $product->id, 2);
        $this->assertSame(['success', 'success'], $this->race([
            ['checkout', $user, $product, $token], ['checkout', $user, $product, $token],
        ]));
        $this->assertSame(1, Order::count());
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_concurrent_cancellation_restores_stock_once(): void
    {
        $user = $this->customer('Admin');
        $order = $this->placedOrder($user);
        $results = $this->race([['cancel', $user, $order], ['cancel', $user, $order]]);
        sort($results);
        $this->assertSame(['rejected', 'success'], $results);
        $this->assertSame('Cancelled', $order->fresh()->status);
        $this->assertSame(10, Product::first()->stock);
    }
}
