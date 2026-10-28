@extends('layouts.admin')

@section('title', 'Tạo Tin Tức Mới')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Tạo Tin Tức Mới</h2>
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

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf

        <div class="form-group row">
            <label for="thumbnail" class="col-md-2 col-form-label">Ảnh Bìa</label>
            <div class="col-md-10">
                <input type="file" name="thumbnail" id="thumbnail" class="form-control-file" accept="image/*" />
                <small class="form-text text-muted">Chọn ảnh bìa đại diện cho tin tức</small>
                @error('thumbnail') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="title" class="col-md-2 col-form-label">Tiêu Đề <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required placeholder="Nhập tiêu đề tin tức..." />
                @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="content" class="col-md-2 col-form-label">Nội Dung / Mô Tả <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <textarea name="content" id="content" class="form-control" rows="8" required placeholder="Nhập nội dung bài viết...">{{ old('content') }}</textarea>
                @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-check mr-1"></i> Tạo mới
                </button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary ml-2">
                    Trở về danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
