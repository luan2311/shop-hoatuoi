<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('published_at', 'desc')->orderBy('_id', 'desc')->paginate(10);

        return view('posts.index', [
            'posts'        => $posts,
            'tinTucNoiBat' => $this->tinTucNoiBat(),
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->orWhere('code', $slug)
            ->orWhere('_id', $slug)
            ->firstOrFail();

        return view('posts.show', [
            'post'           => $post,
            'tinTucLienQuan' => Post::where('slug', '!=', $post->slug)
                ->where('code', '!=', $post->code)
                ->orderBy('published_at', 'desc')
                ->orderBy('_id', 'desc')
                ->take(3)
                ->get(),
            'tinTucNoiBat'   => $this->tinTucNoiBat(),
        ]);
    }

    // Cột "Tin tức nổi bật": 5 bài mới nhất (như Details của đồ án cũ)
    private function tinTucNoiBat()
    {
        return Post::orderBy('published_at', 'desc')->orderBy('_id', 'desc')->take(5)->get();
    }
}
