<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\SalesService;
use Illuminate\Support\Facades\DB;
use Mockery;
use MongoDB\Driver\Exception\CommandException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\Concerns\SalesFixtures;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class CustomerCancelOrderTest extends TestCase
{
    use SalesFixtures, UsesMongoDatabase;

    public function test_owner_can_cancel_pending_cod_once_without_changing_money_snapshots(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $order->forceFill(['subtotal_amount' => 750000, 'discount_amount' => 50000, 'voucher_code' => 'SNAPSHOT'])->save();
        $before = $order->toArray();
        $this->actingAs($user)->get('/account/orders')->assertOk()->assertSee('Hủy đơn hàng');
        $this->get('/account/orders/'.$order->id)->assertOk()->assertSee('Bạn có chắc chắn muốn hủy đơn hàng này không?');
        $this->post('/account/orders/'.$order->id.'/cancel', ['reason' => 'Đổi kế hoạch', 'status' => 'Completed'])
            ->assertRedirect('/account/orders/'.$order->id)->assertSessionHas('status');
        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame(10, Product::first()->stock);
        $this->assertSame($before['items'], $order->items);
        $this->assertSame($before['total_amount'], $order->total_amount);
        foreach (['subtotal_amount', 'discount_amount', 'voucher_code'] as $field) {
            $this->assertSame($before[$field], $order->{$field});
        }
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame('Customer', $order->cancelled_by['type']);
        $this->assertSame($user->id, (string) $order->cancelled_by['user_id']);
        $this->assertSame('Đổi kế hoạch', $order->cancel_reason);
        $this->postJson('/account/orders/'.$order->id.'/cancel')->assertUnprocessable();
        $this->assertSame(10, Product::first()->stock);
        $this->get('/account/orders/'.$order->id)->assertOk()->assertSee('Đã hủy')->assertDontSee('Xác nhận hủy');
        $this->get('/account/orders')->assertOk()->assertDontSee('Xác nhận hủy');
    }

    public static function blockedStates(): array
    {
        return [['Processing'], ['Shipped'], ['Completed'], ['Cancelled']];
    }

    #[DataProvider('blockedStates')]
    public function test_other_states_cannot_be_cancelled(string $status): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $order->update(['status' => $status]);
        $this->actingAs($user)->postJson('/account/orders/'.$order->id.'/cancel')->assertUnprocessable();
        $this->assertSame($status, $order->fresh()->status);
        $this->assertSame(8, Product::first()->stock);
        $this->get('/account/orders/'.$order->id)->assertDontSee('Xác nhận hủy');
    }

    public function test_authentication_ownership_legacy_id_reason_validation_and_csrf(): void
    {
        $owner = $this->customer();
        $order = $this->placedOrder($owner);
        $url = '/account/orders/'.$order->id.'/cancel';
        $this->postJson($url)->assertUnauthorized();
        $this->actingAs($this->customer())->postJson($url)->assertNotFound();
        $this->actingAs($owner)->postJson($url, ['reason' => str_repeat('x', 201)])->assertUnprocessable();
        $this->get($url)->assertMethodNotAllowed();
        $this->app->instance('env', 'local');
        try {
            $this->post($url, ['_token' => 'invalid'])->assertStatus(419);
        } finally {
            $this->app->instance('env', 'testing');
        }
        $order->user_id = $owner->id;
        $order->save();
        $this->post($url)->assertRedirect();
        $this->assertSame('Cancelled', $order->fresh()->status);
    }

    public function test_banking_unknown_payment_and_paid_cod_are_rejected_on_server_and_ui(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $order->update(['payment_method' => 'Banking']);
        $this->actingAs($user)->postJson('/account/orders/'.$order->id.'/cancel')->assertUnprocessable();
        $this->get('/account/orders/'.$order->id)->assertSee('liên hệ Admin')->assertDontSee('Xác nhận hủy');
        $order->payment_method = 'COD';
        $order->payment_status = 'paid';
        $order->save();
        $this->postJson('/account/orders/'.$order->id.'/cancel')->assertUnprocessable();
        $this->assertSame(8, Product::first()->stock);
    }

    public function test_partial_restock_is_rolled_back_when_later_product_is_missing(): void
    {
        $user = $this->customer();
        $sales = app(SalesService::class);
        $first = $this->product();
        $second = $this->product();
        foreach ([$first, $second] as $p) {
            $sales->change($user, 'add', $p->id, 2);
        }
        $order = $sales->checkout($user, $this->delivery());
        $second->delete();
        $this->actingAs($user)->postJson('/account/orders/'.$order->id.'/cancel')->assertUnprocessable();
        $this->assertSame(8, $first->fresh()->stock);
        $this->assertSame('Pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->cancelled_at);
    }

    public function test_transaction_unavailable_cannot_write_or_return_success(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $real = DB::getFacadeRoot();
        $connection = Mockery::mock();
        $connection->shouldReceive('transaction')->once()->andThrow(new CommandException('Transaction numbers are only allowed on a replica set member or mongos'));
        DB::shouldReceive('connection')->once()->with('mongodb')->andReturn($connection);
        try {
            app(SalesService::class)->cancelCustomer($user, $order->id);
            $this->fail('Must propagate transaction failure');
        } catch (CommandException) {
            $this->assertTrue(true);
        } finally {
            DB::swap($real);
        }
        $this->assertSame('Pending', $order->fresh()->status);
        $this->assertSame(8, Product::first()->stock);
    }

    public function test_transaction_callback_is_not_replayed_outside_transaction(): void
    {
        $real = DB::getFacadeRoot();
        $connection = Mockery::mock();
        $calls = 0;
        $connection->shouldReceive('transaction')->once()->andReturnUsing(function ($callback, $attempts) {
            $callback();
            throw new CommandException('Transaction numbers are only allowed on a replica set member or mongos');
        });
        DB::shouldReceive('connection')->once()->with('mongodb')->andReturn($connection);
        $service = new class extends SalesService
        {
            public function probe(callable $cb)
            {
                return $this->runTransaction($cb);
            }
        };
        try {
            $service->probe(function () use (&$calls) {
                $calls++;
            });
            $this->fail('Must fail closed');
        } catch (CommandException) {
            $this->assertSame(1, $calls);
        } finally {
            DB::swap($real);
        }
    }

    private function race($user, $order, array $actions): array
    {
        $jobs = [];
        $start = (string) (microtime(true) + 1);
        foreach ($actions as $action) {
            $p = new Process([PHP_BINARY, base_path('tests/Fixtures/customer-cancel-worker.php'), $action, $user->id, $order->id, $start], base_path(), ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mongodb', 'MONGODB_URI' => config('database.connections.mongodb.dsn'), 'MONGODB_DATABASE' => config('database.connections.mongodb.database'), 'XDEBUG_MODE' => 'off']);
            $p->start();
            $jobs[] = $p;
        }
        $results = [];
        foreach ($jobs as $p) {
            $p->wait();
            $this->assertTrue($p->isSuccessful(), $p->getErrorOutput());
            $results[] = trim($p->getOutput());
        }
        sort($results);

        return $results;
    }

    public function test_simultaneous_customer_cancel_restores_stock_once(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $this->assertSame(['rejected', 'success'], $this->race($user, $order, ['customer', 'customer']));
        $this->assertSame('Cancelled', $order->fresh()->status);
        $this->assertSame(10, Product::first()->stock);
    }

    public function test_admin_processing_racing_customer_cancel_has_one_valid_winner(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        $this->assertSame(['rejected', 'success'], $this->race($user, $order, ['customer', 'processing']));
        $status = $order->fresh()->status;
        $this->assertContains($status, ['Processing', 'Cancelled']);
        $this->assertSame($status === 'Cancelled' ? 10 : 8, Product::first()->stock);
    }

    public function test_order_update_failure_rolls_back_restock_and_cancel_metadata(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        DB::connection('mongodb')->getDatabase()->modifyCollection('orders', [
            'validator' => ['$jsonSchema' => ['bsonType' => 'object', 'required' => ['forced_missing_field']]],
        ]);
        $this->actingAs($user)->postJson('/account/orders/'.$order->id.'/cancel')->assertStatus(503);
        $this->assertSame(8, Product::first()->stock);
        $this->assertSame('Pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->cancelled_at);
    }

    public function test_admin_complete_shortcut_obeys_the_shared_state_machine(): void
    {
        $order = $this->placedOrder($this->customer());
        $this->actingAs($this->customer('Admin'));
        $url = '/admin/orders/'.$order->id.'/complete';
        $this->postJson($url)->assertUnprocessable();
        $order->update(['status' => 'Shipped']);
        $this->post($url)->assertRedirect();
        $this->assertSame('Completed', $order->fresh()->status);
        $this->assertSame(8, Product::first()->stock);
    }

    public function test_admin_shortcut_cannot_overwrite_customer_cancellation(): void
    {
        $user = $this->customer();
        $order = $this->placedOrder($user);
        app(SalesService::class)->cancelCustomer($user, $order->id);
        $this->actingAs($this->customer('Admin'))->postJson('/admin/orders/'.$order->id.'/complete')->assertUnprocessable();
        $this->assertSame('Cancelled', $order->fresh()->status);
        $this->assertSame(10, Product::first()->stock);
    }
}
