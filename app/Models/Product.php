<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'code', 'name', 'price', 'image', 'description',
        'status', 'brand', 'stock', 'slug', 'category_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            $product->slug = Str::slug($product->name) . '-' . strtolower($product->code);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
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
                     ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0)->where('status', 'Còn hàng');
    }
}
