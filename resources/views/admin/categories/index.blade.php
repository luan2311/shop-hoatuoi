@extends('layouts.admin')
@section('title', 'Quản lý Danh Mục')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-categories.css') }}">
@endpush
@section('content')
<x-admin.confirm-modal />
<section class="admin-category-page">
<h1 class="text-center font-weight-bold mb-1">QUẢN LÝ DANH MỤC</h1>
<p class="text-center text-muted mb-4">Quản lý các danh mục sản phẩm trên website</p>
<div class="category-stats">
@foreach([['total','Tổng danh mục','th-large','primary','Tất cả danh mục'],['used','Danh mục có sản phẩm','cube','success','Đang được sử dụng'],['empty','Danh mục chưa có sản phẩm','box-open','warning','Chưa được sử dụng']] as [$key,$label,$icon,$color,$hint])
<div class="category-stat"><span class="category-stat-icon bg-{{ $color }} text-white"><i class="fas fa-{{ $icon }}" aria-hidden="true"></i></span><div><span>{{ $label }}</span><strong data-category-stat="{{ $key }}">{{ $stats[$key] }}</strong><small>{{ $hint }}</small></div></div>
@endforeach
</div>
@if($usage['uncertain'] || $usage['conflicts'])<p class="small text-muted mt-2">Có tham chiếu legacy hoặc không nhất quán. Hệ thống giữ nguyên dữ liệu và chặn xóa khi chưa xác minh được an toàn.</p>@endif
<div class="category-toolbar">
<form action="{{ route('admin.categories.index') }}" method="GET" class="category-filters">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
<div><label for="category-search">Tìm danh mục</label><div class="category-search"><input id="category-search" name="search" value="{{ request('search') }}" maxlength="100" class="form-control" placeholder="Tìm tên, mã hoặc slug..."><button class="btn btn-primary">Tìm kiếm</button></div></div>
<div><label for="category-usage">Trạng thái</label><select class="form-control" id="category-usage" name="usage"><option value="">Tất cả</option><option value="used" @selected(request('usage') === 'used')>Có sản phẩm</option><option value="empty" @selected(request('usage') === 'empty')>Chưa có sản phẩm</option></select></div>
<div><label for="category-sort">Sắp xếp</label><select class="form-control" id="category-sort" name="sort">@foreach(['newest'=>'Mới nhất','alpha-asc'=>'Tên A–Z','alpha-desc'=>'Tên Z–A','count-asc'=>'Số sản phẩm tăng dần','count-desc'=>'Số sản phẩm giảm dần'] as $value=>$label)<option value="{{ $value }}" @selected((request('sort') ?: 'newest') === $value)>{{ $label }}</option>@endforeach</select></div>
<a href="{{ route('admin.categories.index') }}" class="btn btn-light border"><i class="fas fa-sync-alt mr-1" aria-hidden="true"></i>Đặt lại</a>
</form>
<a href="{{ route('admin.categories.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tạo danh mục mới</a>
</div>
<div class="category-table-panel">
<div class="table-responsive" tabindex="0" role="region" aria-label="Bảng danh mục, có thể cuộn ngang">
<table class="table table-light table-striped table-hover mb-0"><thead><tr><th>Mã</th><th>Tên danh mục hoa</th><th>Đường dẫn (Slug)</th><th class="text-center">Số sản phẩm</th><th>Ngày cập nhật</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($categories as $item)
@php $count=$usage['counts'][(string)$item->id]; $reason=$deleteReasons[(string)$item->id]; @endphp
<tr data-category-code="{{ $item->code }}"><td><strong>{{ $item->code ?? '#'.$item->id }}</strong></td><td class="category-name"><strong>{{ $item->name }}</strong></td><td class="text-muted category-slug">/{{ $item->slug }}</td><td class="text-center" data-category-count><span class="badge {{ $count ? 'badge-success' : 'badge-secondary' }} px-2 py-1">{{ $count }}</span></td><td class="text-muted text-nowrap">{{ $item->updated_at?->format('d/m/Y') ?? '—' }}</td><td><span class="badge {{ $count ? 'badge-success' : 'badge-warning' }} category-status">{{ $count ? 'Đang sử dụng' : 'Chưa có sản phẩm' }}</span></td><td><div class="category-actions"><a href="{{ route('admin.categories.edit',$item) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit mr-1" aria-hidden="true"></i>Sửa</a>
@if($reason)
<button type="button" class="category-delete-reason" data-toggle="popover" data-trigger="hover focus" data-container="body" data-placement="auto" data-content="{{ $reason }}" aria-label="{{ $reason }}"><i class="fas fa-info-circle mr-1" aria-hidden="true"></i>Không thể xóa</button>
@else
<form action="{{ route('admin.categories.destroy',$item) }}" method="POST" data-confirm-form data-confirm-transport="fetch" data-confirm-type="danger" data-confirm-title="Xác nhận xóa danh mục" data-confirm-message="Bạn có chắc chắn muốn xóa danh mục {{ $item->name }}? Hành động này không thể hoàn tác." data-confirm-text="Xóa danh mục" data-confirm-subject="{{ $item->code }}"><fieldset data-confirm-gate disabled>@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-trash-alt mr-1" aria-hidden="true"></i>Xóa</button></fieldset></form>
@endif
</div></td></tr>
@empty
<tr><td colspan="7" class="text-center py-4 text-muted">Không có danh mục phù hợp. <a href="{{ route('admin.categories.index') }}">Đặt lại bộ lọc</a></td></tr>
@endforelse
</tbody></table></div>
<x-admin.pagination :paginator="$categories" label="danh mục" />
</div>
</section>
@endsection
@push('scripts')
<script>
(function ($) {
 const page = $('.admin-category-page');
 if ($.fn.popover) {
  page.find('[data-toggle="popover"]').popover({html:false});
  page.on('keydown', function (e) { if(e.key === 'Escape') page.find('[data-toggle="popover"]').popover('hide'); });
  $(document).on('click', function(e) { if(!$(e.target).closest('.category-delete-reason,.popover').length) page.find('[data-toggle="popover"]').popover('hide'); });
 }
 page.find('select').on('change',function(){this.form.requestSubmit();});
})(window.jQuery);
</script>
@endpush
