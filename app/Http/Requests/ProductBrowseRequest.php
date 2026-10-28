<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductBrowseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('q') && $this->has('query')) {
            $this->merge(['q' => $this->input('query')]);
        }
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'string', 'regex:/^[a-f0-9]{24}$/i'],
            'category_slug' => ['nullable', 'string', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'sort' => ['nullable', Rule::in(['price-asc', 'price-desc', 'alpha-asc', 'alpha-desc', 'default'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
