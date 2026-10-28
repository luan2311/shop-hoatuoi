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

    public function getCategoryAttribute($value)
    {
        if (! empty($value)) {
            return $value;
        }

        if ($this->category_id && $this->categoryRecord) {
            return [
                'code' => $this->categoryRecord->code,
                'name' => $this->categoryRecord->name,
                'slug' => $this->categoryRecord->slug,
            ];
        }

        return null;
    }

    public static function resolveImageUrl(?string $image): ?string
    {
        $image = trim((string) $image);
        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, 'products/')) {
            return asset('storage/' . $image);
        }

        if (str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        $candidates = [
            'storage/products/' => public_path('storage/products/' . $image),
            'storage/' => public_path('storage/' . $image),
            'images/products/' => public_path('images/products/' . $image),
            'images/' => public_path('images/' . $image),
            'Images/' => public_path('Images/' . $image),
        ];

        foreach ($candidates as $prefix => $fullPath) {
            if (is_file($fullPath)) {
                return asset($prefix . $image);
            }
        }

        if (preg_match('/\.(?:jpg|jpeg|png|webp|gif|svg)$/i', $image)) {
            return asset('storage/products/' . $image);
        }

        return null;
    }

    public static function resolveItemImageUrl(?string $image, ?string $productCode = null): ?string
    {
        $url = static::resolveImageUrl($image);
        if ($url) {
            return $url;
        }

        if (! empty($productCode)) {
            try {
                $product = static::where('code', $productCode)->first();
                if ($product?->image) {
                    return static::resolveImageUrl($product->image);
                }
            } catch (\Throwable) {
                // Ignore query error if DB is unreachable
            }
        }

        return null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->image);
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
