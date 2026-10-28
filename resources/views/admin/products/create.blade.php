@extends('layouts.admin')

@section('title', 'Tạo sản phẩm mới')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Tạo sản phẩm mới</h2>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Có lỗi xảy ra! Vui lòng kiểm tra lại thông tin:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf

        <div class="form-group row">
            <label for="name" class="col-md-2 col-form-label">Tên sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Nhập tên sản phẩm..." />
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="code" class="col-md-2 col-form-label">Mã sản phẩm</label>
            <div class="col-md-10">
                <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="Để trống để tự động sinh mã (VD: SP001)..." />
                @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="price" class="col-md-2 col-form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="number" name="price" id="price" class="form-control" min="1" value="{{ old('price') }}" required placeholder="VD: 350000" />
                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="image" class="col-md-2 col-form-label">Ảnh sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="file" name="image" id="image" class="form-control-file" accept="image/*" required />
                <small class="form-text text-muted">Chọn ảnh sản phẩm (JPG, PNG, WebP tối đa 2MB)</small>
                @error('image') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="description" class="col-md-2 col-form-label">Mô tả</label>
            <div class="col-md-10">
                <textarea name="description" id="description" class="form-control" rows="5" placeholder="Mô tả thông tin chi tiết sản phẩm...">{{ old('description') }}</textarea>
                @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="status" class="col-md-2 col-form-label">Tình trạng <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <select name="status" id="status" class="form-control" required>
                    <option value="Còn hàng" @selected(old('status') === 'Còn hàng' || old('status') === null)>Còn hàng</option>
                    <option value="Hết hàng" @selected(old('status') === 'Hết hàng')>Hết hàng</option>
                </select>
                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="stock" class="col-md-2 col-form-label">Số lượng tồn <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock', 10) }}" required />
                @error('stock') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="category_id" class="col-md-2 col-form-label">Loại sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">--- Chọn loại sản phẩm ---</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') === (string)$category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-check mr-1"></i> Tạo mới
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ml-2">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
