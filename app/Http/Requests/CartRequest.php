<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => [$this->routeIs('cart.add') ? 'required' : 'sometimes', 'string', 'regex:/^[a-f0-9]{24}$/i'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'remove_from_wishlist' => ['sometimes', 'boolean'],
            'product_code' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
