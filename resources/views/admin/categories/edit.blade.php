@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Chỉnh sửa danh mục</h2>
    <h4 class="text-muted">{{ $category->name }}</h4>
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

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <div class="form-group row">
            <label for="name" class="col-md-2 col-form-label">Tên danh mục <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required />
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="slug" class="col-md-2 col-form-label">Đường dẫn SEO (Slug) <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $category->slug) }}" required />
                @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ml-2">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
