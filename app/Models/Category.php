<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = ['code', 'name', 'slug', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            } else {
                $category->slug = Str::slug($category->slug);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            } else {
                $category->slug = Str::slug($category->slug);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
