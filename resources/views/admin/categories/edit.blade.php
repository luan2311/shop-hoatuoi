@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục hoa')

@section('content')
<div class="container-fluid px-0" style="max-width: 860px;">
    <!-- Breadcrumb & Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="text-muted small mb-1">
                <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-muted">Danh mục</a> / <span class="text-dark fw-medium">Chỉnh sửa</span>
            </div>
            <h3 class="fw-bold mb-0" style="color: #1e3926;">{{ $category->name }}</h3>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Info Banner -->
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 10px;">
        <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
        <div>
            Danh mục này hiện đang có <strong>{{ $category->products()->count() }}</strong> sản phẩm hoa liên kết.
            Thay đổi đường dẫn (slug) có thể ảnh hưởng đến thứ hạng tìm kiếm và bookmark của khách hàng.
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Tên danh mục -->
                <div class="mb-4">
                    <label for="name" class="form-label fw-bold text-dark">
                        Tên danh mục hoa <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fas fa-tag"></i>
                        </span>
                        <input type="text"
                               class="form-control form-control-lg border-start-0 ps-0 @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $category->name) }}"
                               placeholder="Ví dụ: Hoa Lan Hồ Điệp..."
                               required
                               autofocus>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="slug" class="form-label fw-bold text-dark mb-0">
                            Đường dẫn thân thiện (Slug SEO)
                        </label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-success" id="regenSlugBtn">
                            <i class="fas fa-sync-alt me-1"></i>Tạo lại từ tên
                        </button>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small">/categories/</span>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug', $category->slug) }}"
                               placeholder="tu-dong-tao-tu-ten">
                    </div>
                    @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Preview URL Box -->
                <div class="p-3 rounded-3 mb-4" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="small text-muted mb-1"><i class="fas fa-link me-1"></i>Đường dẫn truy cập danh mục:</div>
                    <code class="text-success fw-bold" id="urlPreview">
                        {{ url('/categories') }}/<span id="slugPreview">{{ old('slug', $category->slug) }}</span>
                    </code>
                </div>

                <!-- Meta info -->
                <div class="row g-3 py-3 border-top border-bottom mb-4 small text-muted">
                    <div class="col-sm-6">
                        <strong>Ngày khởi tạo:</strong> {{ $category->created_at ? $category->created_at->format('d/m/Y H:i:s') : '---' }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Lần cập nhật cuối:</strong> {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i:s') : '---' }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light px-4 border">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                        <i class="fas fa-save me-1"></i> Cập nhật thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const slugPreview = document.getElementById('slugPreview');
        const regenSlugBtn = document.getElementById('regenSlugBtn');

        function generateSlug(str) {
            return str
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[đĐ]/g, 'd')
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        slugInput.addEventListener('input', function () {
            slugPreview.textContent = this.value ? generateSlug(this.value) : 'slug';
        });

        if (regenSlugBtn) {
            regenSlugBtn.addEventListener('click', function () {
                const s = generateSlug(nameInput.value);
                slugInput.value = s;
                slugPreview.textContent = s || 'slug';
            });
        }
    });
</script>
@endsection
