<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryBrowseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'usage' => ['nullable', Rule::in(['used', 'empty'])],
            'sort' => ['nullable', Rule::in(['newest', 'alpha-asc', 'alpha-desc', 'count-asc', 'count-desc'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
