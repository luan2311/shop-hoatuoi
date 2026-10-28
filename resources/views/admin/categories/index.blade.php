@extends('layouts.admin')

@section('title', 'Quản lý Danh Mục')

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ DANH MỤC</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="{{ route('admin.categories.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm theo tên hoặc slug..." />
        </div>
        <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
        @if(request('search'))
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
        @endif
    </form>
    <p class="text-right mb-0">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Tạo danh mục mới
        </a>
    </p>
</div>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th>Mã</th>
                    <th>Tên danh mục hoa</th>
                    <th>Đường dẫn (Slug)</th>
                    <th class="text-center">Số sản phẩm</th>
                    <th>Ngày cập nhật</th>
                    <th style="min-width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $item)
                    <tr>
                        <td><strong>{{ $item->code ?? ('#' . $item->id) }}</strong></td>
                        <td>
                            <strong class="text-dark">{{ $item->name }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-light border font-monospace">/{{ $item->slug }}</span>
                        </td>
                        <td class="text-center">
                            @php $count = $item->products_count ?? $item->products()->count(); @endphp
                            <span class="badge {{ $count > 0 ? 'badge-success' : 'badge-secondary' }} px-2 py-1">
                                {{ $count }} hoa
                            </span>
                        </td>
                        <td>{{ $item->updated_at ? $item->updated_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            <a href="{{ route('admin.categories.edit', $item) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Sửa
                            </a>

                            <form method="POST" action="{{ route('admin.categories.destroy', $item) }}" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục &quot;{{ $item->name }}&quot; không?');">
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
                        <td colspan="6" class="text-center py-4 text-muted">Không có danh mục nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Trang {{ $categories->currentPage() }} / {{ $categories->lastPage() }} (Tổng: {{ $categories->total() }} danh mục)
    </div>
    <nav aria-label="Page navigation">
        {{ $categories->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection
