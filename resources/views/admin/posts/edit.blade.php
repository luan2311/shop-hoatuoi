@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Tin Tức')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Chỉnh Sửa Tin Tức</h2>
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

    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('PUT')

        <div class="form-group row">
            <label for="title" class="col-md-2 col-form-label">Tiêu Đề <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}" required />
                @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="content" class="col-md-2 col-form-label">Nội Dung / Mô Tả <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <textarea name="content" id="content" class="form-control" rows="8" required>{{ old('content', $post->content) }}</textarea>
                @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2 col-form-label">Ảnh Bìa</label>
            <div class="col-md-10">
                <input type="text" class="form-control mb-2" value="{{ $post->thumbnail }}" readonly style="background-color: #e9ecef; cursor: not-allowed;" />
                @if(!empty($post->thumbnail))
                    <div class="mb-2">
                        <img src="{{ $post->thumbnail_url }}" alt="Ảnh bìa tin tức" style="max-width: 200px; max-height: 140px; border-radius: 4px; border: 1px solid #ddd; padding: 4px;" />
                    </div>
                @endif
                <input type="file" name="thumbnail" id="thumbnail" class="form-control-file" accept="image/*" />
                <small class="form-text text-muted">Chọn ảnh mới nếu muốn thay đổi ảnh bìa</small>
                @error('thumbnail') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary ml-2">
                    Trở về danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
