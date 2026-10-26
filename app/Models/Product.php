<?php

namespace App\Models;

use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';

    // Danh mục được nhúng trong sản phẩm: category: { code, name, slug }
    protected $fillable = [
        'code', 'name', 'price', 'original_price', 'image', 'description',
        'status', 'brand', 'stock', 'slug', 'category',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            $product->slug ??= Str::slug($product->name) . '-' . strtolower($product->code);
        });
    }

    // Accessor: format giá tiền VND
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' đ';
    }

    // Scopes
    public function scopeSearch($query, string $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%")
                     ->orWhere('category.name', 'like', "%{$keyword}%");
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0)->where('status', 'Còn hàng');
    }

    public function scopeInCategory($query, string $slug)
    {
        return $query->where('category.slug', $slug);
    }
}
