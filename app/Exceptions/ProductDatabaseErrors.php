<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use MongoDB\Driver\Exception\Exception as MongoException;
use Throwable;

class ProductDatabaseErrors
{
    public static function render(Throwable $exception, Request $request)
    {
        if (! $request->routeIs('products.*', 'admin.products.*') ||
            (! $exception instanceof MongoException && ! $exception instanceof QueryException)) {
            return null;
        }
        if ((int) $exception->getCode() === 11000 || (int) $exception->getPrevious()?->getCode() === 11000) {
            $errors = ['code' => ['Mã hoặc slug sản phẩm đã tồn tại.']];
            if ($request->expectsJson()) {
                return response()->json(['message' => $errors['code'][0], 'errors' => $errors], 422);
            }

            return back()->withErrors($errors)->withInput($request->except(['image', '_token', '_method']));
        }
        $message = 'Dữ liệu sản phẩm tạm thời không khả dụng. Vui lòng thử lại sau.';

        return $request->expectsJson() || $request->is('api/*')
            ? response()->json(['message' => $message], 503)
            : response()->view('errors.product-database', compact('message'), 503);
    }
}
