<?php

namespace App\Models;

use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = ['code', 'name', 'slug', 'description'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            $category->slug ??= Str::slug($category->name);
        });
    }

    // Sản phẩm nhúng bản sao danh mục trong field category.slug
    public function products()
    {
        return $this->hasMany(Product::class, 'category.slug', 'slug');
    }
}
