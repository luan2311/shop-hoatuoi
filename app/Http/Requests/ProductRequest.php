<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $product = $this->route('product');
        if (! $this->filled('slug')) {
            $name = $this->input('name');
            $code = $this->input('code');
            $generated = is_string($name) && is_string($code) ? Str::slug($name).'-'.strtolower($code) : null;
            $this->merge(['slug' => $product instanceof Product ? $product->slug : $generated]);
        }
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $code = Rule::unique(Product::class, 'code');
        $slug = Rule::unique(Product::class, 'slug');
        if ($product instanceof Product) {
            $code->ignore($product->getKey(), '_id');
            $slug->ignore($product->getKey(), '_id');
        }

        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', $code],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:300', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug],
            'category_id' => ['required', 'string', 'regex:/^[a-f0-9]{24}$/i', Rule::exists(Category::class, '_id')],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'status' => ['required', Rule::in(['Còn hàng', 'Hết hàng'])],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:50000'],
            'image' => [$this->isMethod('POST') ? 'required' : 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
