<?php

use App\Exceptions\ProductDatabaseErrors;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use MongoDB\Exception\Exception;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', EnsureUserIsActive::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user() && $request->user()->isAdmin()) {
                return route('admin.dashboard');
            }
            if ($request->user() && (
                strtolower((string) $request->user()->username) === 'user_demo'
                || strtolower((string) $request->user()->email) === 'user@shophoatuoi.com'
            )) {
                return route('home');
            }
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (Throwable $exception, Request $request) => ProductDatabaseErrors::render($exception, $request));

        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->routeIs('cart.*', 'checkout', 'checkout.*', 'orders.*', 'admin.orders.*')
                && ($exception instanceof MongoDB\Driver\Exception\Exception
                    || $exception instanceof Exception)) {
                $message = 'Không thể xử lý dữ liệu lúc này. Vui lòng thử lại sau.';

                return $request->expectsJson()
                    ? response()->json(['message' => $message], 503)
                    : response($message, 503);
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
