@extends('layouts.admin')

@section('title', 'Quản lý Tin Tức')

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ TIN TỨC</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="{{ route('admin.posts.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm theo tiêu đề..." />
        </div>
        <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
            <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
        @endif
    </form>
    <p class="text-right mb-0">
        <a href="{{ route('admin.posts.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Tạo tin tức mới
        </a>
    </p>
</div>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th style="width: 100px;">Ảnh bìa</th>
                    <th>Tiêu Đề</th>
                    <th>Ngày Đăng</th>
                    <th style="min-width: 150px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $item)
                    <tr>
                        <td>
                            @if($item->thumbnail_url)
                                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" style="max-width: 80px; max-height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;" />
                            @else
                                <span class="badge badge-light border">No image</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->title }}</strong>
                            <div class="small text-muted">Mã: {{ $item->code }}</div>
                        </td>
                        <td>
                            {{ $item->display_date }}
                        </td>
                        <td>
                            <a href="{{ route('admin.posts.edit', $item) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Sửa
                            </a>

                            <form method="POST" action="{{ route('admin.posts.destroy', $item) }}" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa tin tức này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm ml-1">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Không tìm thấy bài viết tin tức nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Trang {{ $posts->currentPage() }} / {{ $posts->lastPage() }} (Tổng cộng: {{ $posts->total() }} bài viết)
    </div>
    <nav aria-label="Page navigation">
        {{ $posts->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection
