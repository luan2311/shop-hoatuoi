@if($errors->any())<ul role="alert" class="mb-4 text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
@csrf
@php
    $formValue = static function ($field, $fallback = null) {
        $value = old($field, $fallback);
        return is_scalar($value) ? $value : '';
    };
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    @foreach(['code' => 'Mã sản phẩm', 'name' => 'Tên sản phẩm', 'slug' => 'Slug (để trống để tự tạo)', 'price' => 'Giá bán', 'stock' => 'Tồn kho', 'brand' => 'Thương hiệu'] as $field => $label)
        <label class="block">{{ $label }}
            <input name="{{ $field }}" value="{{ $formValue($field, $product->$field) }}" type="{{ in_array($field, ['price', 'stock']) ? 'number' : 'text' }}" @if(in_array($field, ['price', 'stock'])) min="0" step="{{ $field === 'stock' ? '1' : '0.01' }}" @endif @required(in_array($field, ['code', 'name', 'price', 'stock'])) class="mt-1 block w-full rounded border-gray-300">
        </label>
    @endforeach
    <label>Danh mục
        <select name="category_id" required class="mt-1 block w-full rounded border-gray-300">
            <option value="">Chọn danh mục</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected($formValue('category_id', $product->category_id) === (string) $category->id || (!$formValue('category_id', $product->category_id) && data_get($product->category, 'code') === $category->code))>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>
    <label>Tình trạng
        <select name="status" required class="mt-1 block w-full rounded border-gray-300">
            @foreach(['Còn hàng', 'Hết hàng'] as $status)<option @selected($formValue('status', $product->status) === $status)>{{ $status }}</option>@endforeach
        </select>
    </label>
    <label class="sm:col-span-2">Mô tả<textarea name="description" rows="6" class="mt-1 block w-full rounded border-gray-300">{{ $formValue('description', $product->description) }}</textarea></label>
    <label class="sm:col-span-2">Ảnh JPEG, PNG, WebP (tối đa 2 MB)
        @if($product->image_url)<img src="{{ $product->image_url }}" alt="Ảnh hiện tại" class="my-2 h-24 object-cover">@endif
        <input name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(!$product->exists) class="mt-2 block max-w-full">
    </label>
</div>
<button class="mt-6 rounded bg-gray-800 px-4 py-2 text-white">Lưu sản phẩm</button>
<a href="{{ route('admin.products.index') }}" class="ml-4 underline">Quay lại</a>
