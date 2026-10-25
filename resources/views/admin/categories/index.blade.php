@extends('layouts.admin')

@section('title', 'Quản lý danh mục hoa')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1">Quản lý danh mục hoa</h3>
            <p class="text-muted mb-0">Danh sách các danh mục phân loại sản phẩm trong cửa hàng.</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                <i class="bi bi-plus-lg me-1"></i> Thêm danh mục mới
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên hoặc slug danh mục..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-dark">Tìm kiếm</button>
                    @if(request('search'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Tên danh mục</th>
                            <th>Đường dẫn (Slug)</th>
                            <th class="text-center">Số sản phẩm</th>
                            <th>Ngày tạo</th>
                            <th class="text-end" style="width: 160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="text-muted fw-bold">#{{ $category->id }}</td>
                                <td class="fw-semibold text-dark">{{ $category->name }}</td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace">{{ $category->slug }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-info-subtle text-info px-3">
                                        {{ $category->products_count ?? $category->products()->count() }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : '---' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục &quot;{{ $category->name }}&quot; không?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Không tìm thấy danh mục nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-end">
                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
