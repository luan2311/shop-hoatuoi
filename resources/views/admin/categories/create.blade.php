@extends('layouts.admin')

@section('title', 'Thêm danh mục mới')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Tạo danh mục hoa mới</h2>
    <hr />

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <strong>Có lỗi xảy ra! Vui lòng kiểm tra lại:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-4">
        @csrf

        <div class="form-group row">
            <label for="name" class="col-md-2 col-form-label">Tên danh mục <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Ví dụ: Bó Hoa Tươi, Kệ Chúc Mừng..." />
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="slug" class="col-md-2 col-form-label">Đường dẫn SEO (Slug)</label>
            <div class="col-md-10">
                <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug') }}" placeholder="Để trống để tự động sinh từ tên danh mục..." />
                @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-check mr-1"></i> Tạo mới
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ml-2">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
