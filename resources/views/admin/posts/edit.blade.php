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
                        <div class="small text-muted mb-1 font-weight-bold">Ảnh hiện tại:</div>
                        <img src="{{ $post->thumbnail_url }}" alt="Ảnh bìa tin tức" style="max-width: 130px; max-height: 130px; object-fit: cover; border-radius: 6px; border: 1px solid #ced4da; padding: 3px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" />
                    </div>
                @endif
                <input type="file" name="thumbnail" id="thumbnail" class="form-control-file" accept="image/*" />
                <small class="form-text text-muted">Chọn ảnh mới nếu muốn thay đổi ảnh bìa</small>
                
                <!-- Khung xem trước ảnh mới ở góc nhỏ -->
                <div id="thumbnail-preview-wrapper" class="mt-2" style="display: none;">
                    <div class="small text-muted mb-1 font-weight-bold">Xem trước ảnh mới:</div>
                    <div style="position: relative; display: inline-block;">
                        <img id="thumbnail-preview" src="#" alt="Xem trước ảnh bìa mới" style="max-width: 130px; max-height: 130px; object-fit: cover; border-radius: 6px; border: 1px solid #ced4da; padding: 3px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" />
                        <button type="button" id="remove-thumbnail-preview" class="btn btn-sm btn-danger" style="position: absolute; top: -8px; right: -8px; border-radius: 50%; width: 22px; height: 22px; padding: 0; line-height: 20px; font-size: 11px;" title="Hủy ảnh mới">&times;</button>
                    </div>
                </div>

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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const thumbnailInput = document.getElementById('thumbnail');
        const previewWrapper = document.getElementById('thumbnail-preview-wrapper');
        const previewImg = document.getElementById('thumbnail-preview');
        const removeBtn = document.getElementById('remove-thumbnail-preview');

        if (!thumbnailInput || !previewWrapper || !previewImg) return;

        thumbnailInput.addEventListener('change', function () {
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
                thumbnailInput.value = '';
                previewWrapper.style.display = 'none';
                previewImg.src = '#';
            });
        }
    });
</script>
@endpush
