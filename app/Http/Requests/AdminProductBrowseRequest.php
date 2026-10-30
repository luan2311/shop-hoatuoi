<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class AdminProductBrowseRequest extends ProductBrowseRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'sale_state' => ['nullable', Rule::in(['selling', 'stopped', 'out-of-stock'])],
            'sort' => ['nullable', Rule::in(['newest', 'default', 'price-asc', 'price-desc', 'alpha-asc', 'alpha-desc', 'stock-asc', 'stock-desc'])],
        ]);
    }
}
