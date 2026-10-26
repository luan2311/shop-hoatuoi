<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use MongoDB\Laravel\Eloquent\Model;

class Wishlist extends Model
{
    protected $connection = 'mongodb';

    // { user_id, product_code } - unique index (user_id, product_code) trong mongodb_init.js
    protected $fillable = ['user_id', 'product_code'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code', 'code');
    }

    // Wishlist của 1 user (user_id = id đăng nhập của user)
    public function scopeOfUser($query, Authenticatable $user)
    {
        return $query->where('user_id', $user->getAuthIdentifier());
    }

    // Mã các sản phẩm user đã thích; lưu vào request để cả trang chỉ query 1 lần
    public static function productCodesFor(?Authenticatable $user): array
    {
        if (! $user) {
            return [];
        }

        $attributes = request()->attributes;

        if (! $attributes->has('wishlist_product_codes')) {
            $attributes->set('wishlist_product_codes', static::ofUser($user)->pluck('product_code')->all());
        }

        return $attributes->get('wishlist_product_codes');
    }
}
