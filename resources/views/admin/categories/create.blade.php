@extends('layouts.admin')

@section('title', 'Thêm danh mục mới')

@section('content')
<div class="container-fluid px-0" style="max-width: 860px;">
    <!-- Breadcrumb & Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="text-muted small mb-1">
                <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-muted">Danh mục</a> / <span class="text-dark fw-medium">Thêm mới</span>
            </div>
            <h3 class="fw-bold mb-0" style="color: #1e3926;">Thêm danh mục mới</h3>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

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
                               value="{{ old('name') }}"
                               placeholder="Ví dụ: Bó Hoa Tươi, Kệ Chúc Mừng, Hoa Lan Hồ Điệp..."
                               required
                               autofocus>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label for="slug" class="form-label fw-bold text-dark">
                        Đường dẫn thân thiện (Slug SEO)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small">/categories/</span>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug') }}"
                               placeholder="tu-dong-tao-tu-ten-neu-de-trong">
                    </div>
                    <div class="form-text text-muted small mt-1">
                        <i class="fas fa-info-circle me-1"></i>Hệ thống sẽ tự động chuyển đổi tên danh mục thành slug không dấu chuẩn SEO nếu bạn để trống.
                    </div>
                    @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Preview URL Box -->
                <div class="p-3 rounded-3 mb-4" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="small text-muted mb-1"><i class="fas fa-link me-1"></i>Xem trước liên kết truy cập ngoài website:</div>
                    <code class="text-success fw-bold" id="urlPreview">
                        {{ url('/categories') }}/<span id="slugPreview">{{ old('slug', 'ten-danh-muc') }}</span>
                    </code>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light px-4 border">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                        <i class="fas fa-save me-1"></i> Lưu danh mục
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

        let userTouchedSlug = false;

        slugInput.addEventListener('input', function () {
            userTouchedSlug = this.value.trim().length > 0;
            slugPreview.textContent = this.value ? generateSlug(this.value) : 'ten-danh-muc';
        });

        nameInput.addEventListener('input', function () {
            if (!userTouchedSlug) {
                const s = generateSlug(this.value);
                slugInput.value = s;
                slugPreview.textContent = s || 'ten-danh-muc';
            }
        });
    });
</script>
@endsection
