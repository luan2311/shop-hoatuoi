<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['thumbnail', 'title', 'slug', 'content', 'published_at'];

    protected $casts = ['published_at' => 'date'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            $post->slug = Str::slug($post->title) . '-' . now()->timestamp;
        });
    }
}
