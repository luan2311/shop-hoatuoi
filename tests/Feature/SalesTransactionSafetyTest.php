<?php

namespace Tests\Feature;

use App\Services\SalesService;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\CommandException;
use Tests\TestCase;

class SalesTransactionSafetyTest extends TestCase
{
    public function test_failure_after_callback_started_does_not_replay_callback(): void
    {
        $failure = new CommandException('Transaction numbers are only allowed on a replica set member or mongos');
        $connection = \Mockery::mock();
        $connection->shouldReceive('transaction')->once()->andReturnUsing(function (callable $callback, int $attempts) use ($failure): void {
            $this->assertSame(5, $attempts);
            $callback();
            throw $failure;
        });
        DB::shouldReceive('connection')->once()->with('mongodb')->andReturn($connection);
        $sales = new class extends SalesService
        {
            public function probe(callable $callback): mixed
            {
                return $this->runTransaction($callback);
            }
        };
        $calls = 0;
        try {
            $sales->probe(function () use (&$calls): void {
                $calls++;
            });
            $this->fail('Transaction failure must propagate even after the callback started.');
        } catch (CommandException $caught) {
            $this->assertSame($failure, $caught);
        }
        $this->assertSame(1, $calls, 'Never replay a failed transaction outside its boundary.');
    }

    public function test_standalone_failure_never_replays_writes_outside_transaction(): void
    {
        $failure = new CommandException('Transaction numbers are only allowed on a replica set member or mongos');
        $connection = \Mockery::mock();
        $connection->shouldReceive('transaction')->once()->andThrow($failure);
        DB::shouldReceive('connection')->once()->with('mongodb')->andReturn($connection);
        $sales = new class extends SalesService
        {
            public function probe(callable $callback): mixed
            {
                return $this->runTransaction($callback);
            }
        };
        $writes = 0;
        try {
            $sales->probe(function () use (&$writes): void {
                $writes++;
            });
            $this->fail('A transaction failure must propagate.');
        } catch (CommandException $caught) {
            $this->assertSame($failure, $caught);
        } finally {
            $this->assertSame(0, $writes, 'Never execute sales writes outside a transaction.');
        }
    }
}
