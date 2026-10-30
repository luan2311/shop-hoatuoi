<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPagination
{
    public const SIZES = [5, 10, 20, 50, 100];

    public static function perPage(Request $request): int
    {
        $data = $request->validate([
            'per_page' => ['nullable', 'integer', Rule::in(self::SIZES)],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        return (int) ($data['per_page'] ?? 10);
    }
}
