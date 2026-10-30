<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = AdminPagination::perPage($request);
        $search = $request->input('searchString') ?? $request->input('q');
        $query = Post::query();

        if (!empty($search)) {
            $escaped = '%' . addcslashes($search, '\\%_') . '%';
            $query->where('title', 'like', $escaped);
        }

        $posts = $query->orderBy('published_at', 'desc')->orderBy('_id', 'desc')->paginate($perPage)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề tin tức.',
            'content.required' => 'Vui lòng nhập nội dung mô tả tin tức.',
        ]);

        $thumbnailName = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $thumbnailName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $thumbnailName);
        }

        // Auto generate code TT001...
        $lastPost = Post::where('code', 'regex', '/^TT\d+/i')->orderBy('code', 'desc')->first();
        $nextNum = 1;
        if ($lastPost && preg_match('/^TT(\d+)/i', $lastPost->code, $m)) {
            $nextNum = (int)$m[1] + 1;
        }
        $code = 'TT' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

        Post::create([
            'code' => $code,
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('title')) . '-' . time(),
            'summary' => Str::limit(strip_tags($request->input('content')), 180),
            'content' => $request->input('content'),
            'thumbnail' => $thumbnailName,
            'published_at' => now(),
        ]);

        return redirect()->route('admin.posts.index')->with('success', 'Tạo tin tức mới thành công!');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $post->title = $request->input('title');
        $post->content = $request->input('content');
        $post->summary = Str::limit(strip_tags($request->input('content')), 180);

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $thumbnailName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $thumbnailName);
            $post->thumbnail = $thumbnailName;
        }

        $post->save();

        return redirect()->route('admin.posts.index')->with('success', 'Cập nhật tin tức thành công!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Đã xóa tin tức thành công!');
    }
}
