<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'uuid'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'receiver_phone' => ['required', 'string', 'regex:/^0[0-9]{9,10}$/'],
            'receiver_address' => ['required', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:COD,Banking'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
