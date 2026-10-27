@extends('layouts.admin')

@section('title', 'Quản lý danh mục hoa')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1" style="color: #1e3926;">Quản lý danh mục hoa</h3>
            <p class="text-muted mb-0">Danh sách các danh mục phân loại hoa tươi tại cửa hàng Góc Hoa Xinh</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-success shadow-sm" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                <i class="fas fa-plus me-1"></i> Thêm danh mục mới
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm kiếm theo tên danh mục hoặc slug..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-dark px-3">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> Đặt lại
                        </a>
                    @endif
                </div>
                <div class="col-md text-md-end text-muted small">
                    Tổng cộng: <strong class="text-dark">{{ $categories->total() }}</strong> danh mục
                </div>
            </form>
        </div>
    </div>

    <!-- Table List -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #edf2f7;">
                        <tr>
                            <th class="ps-4" style="width: 80px;">Mã</th>
                            <th>Tên danh mục hoa</th>
                            <th>Đường dẫn (Slug)</th>
                            <th class="text-center" style="width: 160px;">Số sản phẩm</th>
                            <th>Ngày cập nhật</th>
                            <th class="text-end pe-4" style="width: 140px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="ps-4 text-secondary fw-bold">#{{ $category->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px; background-color: #eaf3eb;">
                                            <i class="fas fa-spa"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $category->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                        /categories/{{ $category->slug }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php $count = $category->products_count ?? $category->products()->count(); @endphp
                                    <span class="badge rounded-pill {{ $count > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-1 fw-bold">
                                        {{ $count }} hoa
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i') : ($category->created_at ? $category->created_at->format('d/m/Y') : '---') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa danh mục">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục hoa &quot;{{ $category->name }}&quot; không?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa danh mục">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-leaf fs-1 text-secondary mb-3 d-block opacity-50"></i>
                                    <h5>Không tìm thấy danh mục hoa nào</h5>
                                    <p class="small text-muted mb-3">Hãy tạo danh mục mới để phân loại các sản phẩm hoa tươi của cửa hàng.</p>
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-success btn-sm px-3" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                                        <i class="fas fa-plus me-1"></i> Thêm danh mục mới
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Hiển thị {{ $categories->firstItem() }} - {{ $categories->lastItem() }} trên {{ $categories->total() }} danh mục
                    </span>
                    <div>
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
