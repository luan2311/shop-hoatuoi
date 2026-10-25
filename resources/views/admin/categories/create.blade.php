@extends('layouts.admin')

@section('title', 'Thêm danh mục mới')

@section('content')
<div class="container-fluid" style="max-width: 800px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1">Thêm danh mục mới</h3>
            <p class="text-muted mb-0">Tạo danh mục mới để phân loại các sản phẩm hoa tươi.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Hoa Lan Hồ Điệp, Hoa Khai Trương..." required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-semibold">Đường dẫn thân thiện (Slug)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small">/categories/</span>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Tự động tạo từ tên nếu để trống">
                    </div>
                    <div class="form-text text-muted">Nếu để trống, hệ thống sẽ tự động tạo slug chuẩn SEO không dấu từ tên danh mục.</div>
                    @error('slug')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                        <i class="bi bi-check2-circle me-1"></i> Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
