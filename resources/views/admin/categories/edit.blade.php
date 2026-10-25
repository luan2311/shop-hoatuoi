@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="container-fluid" style="max-width: 800px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1">Chỉnh sửa danh mục</h3>
            <p class="text-muted mb-0">Cập nhật thông tin danh mục hoa tươi #{{ $category->id }}.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-semibold">Đường dẫn thân thiện (Slug)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small">/categories/</span>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $category->slug) }}">
                    </div>
                    <div class="form-text text-muted">Đường dẫn dùng trong URL liên kết danh mục.</div>
                    @error('slug')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <div class="text-muted small">
                        Số lượng sản phẩm: <strong>{{ $category->products()->count() }}</strong>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-4" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                            <i class="bi bi-check2-circle me-1"></i> Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
