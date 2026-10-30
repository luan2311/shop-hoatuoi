@php
    $oldInput = static function (string $key, mixed $default = ''): mixed {
        $value = old($key, $default);
        return is_scalar($value) || $value === null ? $value : $default;
    };
@endphp
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
                <input type="text" name="name" id="name" class="form-control" value="{{ $oldInput('name') }}" required placeholder="Nhập tên sản phẩm..." />
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="code" class="col-md-2 col-form-label">Mã sản phẩm</label>
            <div class="col-md-10">
                <input type="text" name="code" id="code" class="form-control" value="{{ $oldInput('code') }}" placeholder="Để trống để tự động sinh mã (VD: SP001)..." />
                @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="price" class="col-md-2 col-form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="number" name="price" id="price" class="form-control" min="1" value="{{ $oldInput('price') }}" required placeholder="VD: 350000" />
                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="image" class="col-md-2 col-form-label">Ảnh sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="file" name="image" id="image" class="form-control-file" accept="image/*" required />
                <small class="form-text text-muted">Chọn ảnh sản phẩm (JPG, PNG, WebP tối đa 2MB)</small>
                
                <!-- Khung xem trước ảnh kích thước nhỏ -->
                <div id="image-preview-wrapper" class="mt-2" style="display: none;">
                    <div class="small text-muted mb-1 font-weight-bold">Xem trước ảnh:</div>
                    <div style="position: relative; display: inline-block;">
                        <img id="image-preview" src="#" alt="Xem trước ảnh" style="max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; padding: 3px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.08);" />
                        <button type="button" id="remove-preview-btn" class="btn btn-sm btn-danger" style="position: absolute; top: -8px; right: -8px; border-radius: 50%; width: 22px; height: 22px; padding: 0; line-height: 20px; font-size: 11px;" title="Xóa ảnh đã chọn">&times;</button>
                    </div>
                </div>

                @error('image') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="description" class="col-md-2 col-form-label">Mô tả</label>
            <div class="col-md-10">
                <textarea name="description" id="description" class="form-control" rows="5" placeholder="Mô tả thông tin chi tiết sản phẩm...">{{ $oldInput('description') }}</textarea>
                @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="status" class="col-md-2 col-form-label">Tình trạng <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <select name="status" id="status" class="form-control" required>
                    <option value="Còn hàng" @selected($oldInput('status') === 'Còn hàng' || $oldInput('status') === null)>Còn hàng</option>
                    <option value="Hết hàng" @selected($oldInput('status') === 'Hết hàng')>Hết hàng</option>
                </select>
                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="stock" class="col-md-2 col-form-label">Số lượng tồn <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ $oldInput('stock', 10) }}" required />
                <small class="form-text text-muted">Số lượng sản phẩm trong kho (khách hàng không thể tăng số lượng trong giỏ hàng vượt quá tồn kho này)</small>
                @error('stock') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="category_id" class="col-md-2 col-form-label">Loại sản phẩm <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">--- Chọn loại sản phẩm ---</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($oldInput('category_id') === (string)$category->id)>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const imageInput = document.getElementById('image');
        const previewWrapper = document.getElementById('image-preview-wrapper');
        const previewImg = document.getElementById('image-preview');
        const removeBtn = document.getElementById('remove-preview-btn');

        if (!imageInput || !previewWrapper || !previewImg) return;

        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('Vui lòng chọn một tệp hình ảnh hợp lệ (JPG, PNG, WebP).');
                    this.value = '';
                    previewWrapper.style.display = 'none';
                    previewImg.src = '#';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewWrapper.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewWrapper.style.display = 'none';
                previewImg.src = '#';
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                imageInput.value = '';
                previewWrapper.style.display = 'none';
                previewImg.src = '#';
            });
        }
    });
</script>
@endpush
