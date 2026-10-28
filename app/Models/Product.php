<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MongoDB\BSON\Regex;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'code', 'name', 'price', 'original_price', 'image', 'description',
        'status', 'brand', 'stock', 'slug', 'category_id', 'category',
    ];

    protected function casts(): array
    {
        return ['stock' => 'integer'];
    }

    public function getPriceAttribute($value): int|float|null
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            $str = (string) $value;
            return str_contains($str, '.') ? (float) $str : (int) $str;
        }

        if ($value === null) {
            return null;
        }

        return is_numeric($value) && (int) $value == $value ? (int) $value : (float) $value;
    }

    public function getOriginalPriceAttribute($value): int|float|null
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            $str = (string) $value;
            return str_contains($str, '.') ? (float) $str : (int) $str;
        }

        if ($value === null) {
            return null;
        }

        return is_numeric($value) && (int) $value == $value ? (int) $value : (float) $value;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            $product->slug ??= Str::slug($product->name).'-'.strtolower($product->code);
        });
    }

    // category is an embedded snapshot; use this relation for the live category.
    public function categoryRecord()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = (string) $this->image;
        if (preg_match('~^products/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$~i', $image)) {
            return Storage::disk('public')->url($image);
        }
        if ($image !== '' && basename($image) === $image && preg_match('/\.(jpg|jpeg|png|webp)$/i', $image)) {
            foreach (['images/products/', 'Images/'] as $directory) {
                if (is_file(public_path($directory.$image))) {
                    return asset($directory.$image);
                }
            }
        }

        return null;
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
        return $this->hasMany(Wishlist::class, 'product_code', 'code');
    }

    // Accessor: format giá tiền VND
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', '.').' đ';
    }

    // Scopes
    public function scopeMatchingKeyword($query, string $keyword)
    {
        $literal = new Regex(preg_quote($keyword), 'i');

        return $query->where(function ($query) use ($literal) {
            $query->where('name', 'regex', $literal)
                ->orWhere('description', 'regex', $literal)
                ->orWhere('category.name', 'regex', $literal);
        });
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
