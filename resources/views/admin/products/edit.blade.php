@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Chỉnh sửa sản phẩm</h2>
    <h4 class="text-muted">{{ $product->name }} (Mã: {{ $product->code }})</h4>
    <hr />

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <strong>Có lỗi xảy ra! Vui lòng kiểm tra lại thông tin:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('PUT')

        <div class="form-group row">
            <label for="name" class="col-md-2 col-form-label">Tên sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}" required />
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="code" class="col-md-2 col-form-label">Mã sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $product->code) }}" required />
                @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="price" class="col-md-2 col-form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="number" name="price" id="price" class="form-control" min="1" value="{{ old('price', (int)$product->price) }}" required />
                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2 col-form-label">Ảnh Sản Phẩm</label>
            <div class="col-md-10">
                <input type="text" class="form-control mb-2" value="{{ $product->image }}" readonly style="background-color: #e9ecef; cursor: not-allowed;" />
                @if($product->image_url)
                    <div class="mb-2">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-width: 150px; max-height: 120px; border-radius: 4px; border: 1px solid #ddd; padding: 4px;" />
                    </div>
                @endif
                <input type="file" name="image" id="image" class="form-control-file" accept="image/*" />
                <small class="form-text text-muted">Chọn ảnh mới nếu muốn thay đổi ảnh đại diện sản phẩm</small>
                @error('image') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="description" class="col-md-2 col-form-label">Mô tả</label>
            <div class="col-md-10">
                <textarea name="description" id="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
                @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="status" class="col-md-2 col-form-label">Tình trạng <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <select name="status" id="status" class="form-control" required>
                    <option value="Còn hàng" @selected(old('status', $product->status) === 'Còn hàng')>Còn hàng</option>
                    <option value="Hết hàng" @selected(old('status', $product->status) === 'Hết hàng')>Hết hàng</option>
                </select>
                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="stock" class="col-md-2 col-form-label">Số lượng tồn <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock', $product->stock) }}" required />
                @error('stock') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="category_id" class="col-md-2 col-form-label">Loại sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">--- Chọn loại sản phẩm ---</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) === (string)$category->id || old('category_id', data_get($product->category, 'code')) === $category->code)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-danger ml-2">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
