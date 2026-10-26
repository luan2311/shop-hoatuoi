<?php

namespace App\Models;

use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

class Post extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = ['code', 'thumbnail', 'title', 'slug', 'summary', 'content', 'published_at'];

    protected $casts = ['published_at' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            $post->slug ??= Str::slug($post->title) . '-' . now()->timestamp;
        });
    }
}
