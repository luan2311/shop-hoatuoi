<?php

namespace Tests\Unit;

use App\Services\VoucherService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VoucherServiceTest extends TestCase
{
    public function test_existing_codes_use_integer_vnd_and_discount_is_capped(): void
    {
        $vouchers = new VoucherService;
        $this->assertSame(['subtotal_amount' => 2100000, 'discount_amount' => 50000, 'total_amount' => 2050000, 'voucher_code' => 'HOATUOI2026'], $vouchers->totals(2100000, ' hoatuoi2026 '));
        $this->assertSame(0, $vouchers->totals(20000, 'HOATUOI2026')['total_amount']);
        $this->assertSame(20000, $vouchers->totals(20000, 'HOATUOI2026')['discount_amount']);
        $this->assertSame(0, $vouchers->totals(0, 'HOATUOI2026')['discount_amount']);
        $this->assertSame(2100000, $vouchers->totals(2100000, 'FREESHIP')['total_amount']);
        $this->assertSame(0, $vouchers->totals(2100000, 'FREESHIP')['discount_amount']);
        $this->assertNull($vouchers->totals(2100000, ' ')['voucher_code']);
        $this->assertSame(2100000, $vouchers->totals(2100000, null)['total_amount']);
    }

    public function test_unknown_code_cannot_discount_an_order(): void
    {
        $this->expectException(ValidationException::class);
        (new VoucherService)->totals(2100000, 'FAKE');
    }
}
