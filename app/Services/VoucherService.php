<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class VoucherService
{
    public function totals(int $subtotal, ?string $code = null): array
    {
        $code = strtoupper(trim($code ?? ''));
        // Preserve the existing checkout offers. Shipping is already free.
        $discount = match ($code) {
            '', 'FREESHIP' => 0,
            'HOATUOI2026' => min(50000, $subtotal),
            default => throw ValidationException::withMessages([
                'voucher_code' => 'Mã ưu đãi không hợp lệ.',
            ]),
        };

        return [
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $subtotal - $discount,
            'voucher_code' => $code !== '' ? $code : null,
        ];
    }
}
