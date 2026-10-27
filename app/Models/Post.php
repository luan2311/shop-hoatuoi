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

    // Ảnh bìa trong storage/app/public/posts; bài chưa có ảnh thì dùng ảnh mặc định
    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? asset('storage/posts/' . $this->thumbnail)
            : asset('images/slide-giao-hoa-2h.jpg');
    }

    // Ngày đăng dạng dd/mm/yyyy (chưa có published_at thì lấy ngày tạo)
    public function getDisplayDateAttribute(): string
    {
        return optional($this->published_at ?? $this->created_at)->format('d/m/Y') ?? '';
    }

    // Đoạn tóm tắt: dùng summary nếu có, không thì bỏ thẻ HTML của nội dung rồi cắt ngắn
    public function getExcerptAttribute(): string
    {
        $text = $this->summary ?: strip_tags((string) $this->content);

        return Str::limit(trim(preg_replace('/\s+/', ' ', $text)), 186);
    }
}
