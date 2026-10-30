@if(isset($errors) && $errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@csrf

<div class="form-group row">
    <label for="name" class="col-md-2 col-form-label">Tên sản phẩm <span class="text-danger">*</span></label>
    <div class="col-md-10">
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required />
    </div>
</div>

<div class="form-group row">
    <label for="code" class="col-md-2 col-form-label">Mã sản phẩm</label>
    <div class="col-md-10">
        <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $product->code ?? '') }}" />
    </div>
</div>

<div class="form-group row">
    <label for="price" class="col-md-2 col-form-label">Giá bán <span class="text-danger">*</span></label>
    <div class="col-md-10">
        <input type="number" name="price" id="price" class="form-control" min="0" value="{{ old('price', (int)($product->price ?? 0)) }}" required />
    </div>
</div>

<div class="form-group row">
    <label for="category_id" class="col-md-2 col-form-label">Loại sản phẩm <span class="text-danger">*</span></label>
    <div class="col-md-10">
        <select name="category_id" id="category_id" class="form-control" required>
            <option value="">--- Chọn loại sản phẩm ---</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') === (string)$category->id || old('category_id', data_get($product->category ?? [], 'code')) === $category->code)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="status" class="col-md-2 col-form-label">Tình trạng <span class="text-danger">*</span></label>
    <div class="col-md-10">
        <select name="status" id="status" class="form-control" required>
            <option value="Còn hàng" @selected(old('status', $product->status ?? 'Còn hàng') === 'Còn hàng')>Còn hàng</option>
            <option value="Hết hàng" @selected(old('status', $product->status ?? '') === 'Hết hàng')>Hết hàng</option>
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="stock" class="col-md-2 col-form-label">Số lượng tồn <span class="text-danger">*</span></label>
    <div class="col-md-10">
        <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock', $product->stock ?? 10) }}" required />
    </div>
</div>

<div class="form-group row">
    <label for="description" class="col-md-2 col-form-label">Mô tả</label>
    <div class="col-md-10">
        <textarea name="description" id="description" class="form-control" rows="5">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label">Ảnh sản phẩm</label>
    <div class="col-md-10">
        @if(!empty($product->image_url))
            <div class="mb-2">
                <img src="{{ $product->image_url }}" alt="Ảnh sản phẩm" style="max-width: 120px; border-radius: 4px;" />
            </div>
        @endif
        <input type="file" name="image" id="image" class="form-control-file" accept="image/*" @required(empty($product?->exists)) />
    </div>
</div>

<div class="form-group row mt-4">
    <div class="col-md-10 offset-md-2">
        <button type="submit" class="btn btn-primary px-4">Lưu sản phẩm</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ml-2">Quay lại</a>
    </div>
</div>
