<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Chuyển khoản chưa được hỗ trợ. Vui lòng chọn thanh toán khi nhận hàng (COD).',
            'voucher.prohibited' => 'Mã ưu đãi chưa được hỗ trợ.',
            'voucher_code.prohibited' => 'Mã ưu đãi chưa được hỗ trợ.',
        ];
    }

    public function rules(): array
    {
        return [
            'voucher' => ['prohibited'],
            'voucher_code' => ['prohibited'],
            'checkout_token' => ['required', 'uuid'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'receiver_phone' => ['required', 'string', 'regex:/^0[0-9]{9,10}$/'],
            'receiver_address' => ['required', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:COD'],
        ];
    }
}
