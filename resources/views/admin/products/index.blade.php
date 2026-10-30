@extends('layouts.admin')
@section('title', 'Quản lý Sản phẩm')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}">
@endpush
@section('content')
<x-admin.confirm-modal />
<section class="admin-product-page" aria-labelledby="product-management-title">
    <h1 id="product-management-title" class="product-page-title">QUẢN LÝ SẢN PHẨM</h1>
    <div class="product-stat-grid" aria-label="Thống kê toàn bộ sản phẩm">
        @foreach([
            ['key' => 'total', 'label' => 'Tổng sản phẩm', 'icon' => 'box', 'color' => 'blue', 'state' => ''],
            ['key' => 'selling', 'label' => 'Đang bán', 'icon' => 'shopping-cart', 'color' => 'green', 'state' => 'selling'],
            ['key' => 'stopped', 'label' => 'Ngừng bán', 'icon' => 'pause-circle', 'color' => 'amber', 'state' => 'stopped'],
            ['key' => 'out-of-stock', 'label' => 'Hết hàng', 'icon' => 'box-open', 'color' => 'red', 'state' => 'out-of-stock'],
        ] as $stat)
            <a class="product-stat-card" data-stat="{{ $stat['key'] }}" href="{{ route('admin.products.index', array_filter(['sale_state' => $stat['state']])) }}" aria-label="{{ $stat['label'] }}: {{ $stats[$stat['key']] }}">
                <span class="stat-icon stat-icon-{{ $stat['color'] }}" aria-hidden="true"><i class="fas fa-{{ $stat['icon'] }}"></i></span>
                <span><span class="stat-label">{{ $stat['label'] }}</span><strong class="stat-value">{{ $stats[$stat['key']] }}</strong></span>
                <i class="fas fa-chevron-right stat-chevron" aria-hidden="true"></i>
            </a>
        @endforeach
    </div>
    <p class="product-stat-note">Thống kê toàn bộ danh sách. Hết hàng (tồn kho = 0) có thể đồng thời thuộc Đang bán hoặc Ngừng bán.</p>

    <div class="product-toolbar">
        <form action="{{ route('admin.products.index') }}" method="GET" class="product-filter-form" aria-label="Lọc sản phẩm">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            <div class="product-filter-search">
                <label for="admin-product-keyword">Tìm sản phẩm</label>
                <div class="product-search-controls">
                    <input id="admin-product-keyword" type="search" name="q" value="{{ request('q', request('searchString', request('query'))) }}" maxlength="100" class="form-control" placeholder="Tên hoặc mã sản phẩm...">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
            </div>
            <div class="product-filter-field">
                <label for="admin-product-category">Danh mục</label>
                <select id="admin-product-category" name="category_id" class="form-control">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') === (string) $category->id || (!request('category_id') && request('category_slug') === $category->slug))>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="product-filter-field">
                <label for="admin-product-state">Trạng thái</label>
                <select id="admin-product-state" name="sale_state" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(['selling' => 'Đang bán', 'stopped' => 'Ngừng bán', 'out-of-stock' => 'Hết hàng'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('sale_state') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="product-filter-field">
                <label for="admin-product-sort">Sắp xếp</label>
                <select id="admin-product-sort" name="sort" class="form-control">
                    @foreach(['newest' => 'Mới nhất', 'price-asc' => 'Giá tăng dần', 'price-desc' => 'Giá giảm dần', 'alpha-asc' => 'Tên A–Z', 'alpha-desc' => 'Tên Z–A', 'stock-asc' => 'Tồn kho tăng dần', 'stock-desc' => 'Tồn kho giảm dần'] as $value => $label)
                        <option value="{{ $value }}" @selected((request('sort') ?: 'newest') === $value || (request('sort') === 'default' && $value === 'alpha-asc'))>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light product-reset"><i class="fas fa-sync-alt mr-1" aria-hidden="true"></i>Đặt lại</a>
        </form>
        <a href="{{ route('admin.products.create') }}" class="btn btn-success product-create"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tạo sản phẩm mới</a>
    </div>
    <p class="d-md-none small text-muted">Vuốt ngang bảng để xem thêm thông tin và thao tác.</p>
    <div class="product-table-panel">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Bảng sản phẩm, có thể cuộn ngang">
            <table class="table product-management-table {{ $products->isEmpty() ? 'is-empty' : '' }}">
                <thead><tr><th scope="col">Ảnh</th><th scope="col">Tên Sản Phẩm</th><th scope="col" class="text-right">Giá Bán</th><th scope="col" class="text-center">Tồn kho</th><th scope="col">Phân loại</th><th scope="col">Thao tác</th></tr></thead>
                <tbody>
                    @forelse($products as $item)
                        @php
                            $deleteReason = $deleteReasons[(string) $item->id];
                            $preview = ['name' => $item->name, 'code' => $item->code, 'price' => $item->formatted_price, 'category' => data_get($item->category, 'name', 'Chưa phân loại'), 'stock' => $item->stock, 'status' => $item->sale_label, 'selling' => $item->is_selling ? 'Đang bán' : 'Ngừng bán', 'image' => $item->image_url];
                        @endphp
                        <tr data-product-code="{{ $item->code }}">
                            <td class="product-image-cell">
                                @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->name }}" width="64" height="64" loading="lazy">@else<span class="product-no-image">Chưa có ảnh</span>@endif
                            </td>
                            <td class="product-name-cell">
                                <button type="button" class="product-preview-name" data-toggle="modal" data-target="#adminProductPreview" data-preview="{{ json_encode($preview, JSON_UNESCAPED_UNICODE) }}" aria-label="Xem nhanh {{ $item->name }}">{{ $item->name }} <i class="far fa-eye" aria-hidden="true"></i></button>
                                <span class="product-code">Mã: {{ $item->code }}</span>
                                <span class="badge product-status-badge {{ !$item->is_selling ? 'badge-warning' : ($item->stock == 0 || $item->status === 'Hết hàng' ? 'badge-danger' : 'badge-success') }}">{{ $item->sale_label }}</span>
                            </td>
                            <td class="product-price-cell text-right">{{ number_format((float) $item->price, 0, ',', '.') }} VNĐ</td>
                            <td class="text-center {{ $item->stock == 0 ? 'text-danger font-weight-bold' : '' }}">{{ $item->stock }}</td>
                            <td class="product-category-cell">{{ data_get($item->category, 'name', 'Chưa phân loại') }}</td>
                            <td class="product-actions-cell">
                                <div class="product-actions">
                                    <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit mr-1" aria-hidden="true"></i>Sửa</a>
                                    <form method="POST" action="{{ route($item->is_selling ? 'admin.products.stop-selling' : 'admin.products.resume-selling', $item) }}" data-confirm-form data-confirm-transport="fetch" data-confirm-type="{{ $item->is_selling ? 'warning' : 'success' }}" data-confirm-title="{{ $item->is_selling ? 'Xác nhận ngừng bán' : 'Xác nhận bán lại' }}" data-confirm-message="{{ $item->is_selling ? 'Sản phẩm sẽ tạm ngừng kinh doanh. Bạn có thể bật bán lại sau.' : 'Bạn có muốn tiếp tục kinh doanh sản phẩm này?' }}" data-confirm-text="{{ $item->is_selling ? 'Ngừng bán' : 'Bán lại' }}" data-confirm-subject="{{ $item->name }} · {{ $item->code }}">
                                        <fieldset data-confirm-gate disabled>
                                        @csrf<button class="btn btn-warning btn-sm"><i class="fas fa-{{ $item->is_selling ? 'pause-circle' : 'play-circle' }} mr-1" aria-hidden="true"></i>{{ $item->is_selling ? 'Ngừng bán' : 'Bán lại' }}</button>
                                        </fieldset>
                                    </form>
                                    @if(!$deleteReason)
                                        <form method="POST" action="{{ route('admin.products.destroy', $item) }}" data-confirm-form data-confirm-transport="fetch" data-confirm-type="danger" data-confirm-title="Xác nhận xóa sản phẩm" data-confirm-message="Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm này? Hành động này không thể hoàn tác." data-confirm-text="Xóa sản phẩm" data-confirm-subject="{{ $item->name }} · {{ $item->code }}">
                                        <fieldset data-confirm-gate disabled>
                                            @csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt mr-1" aria-hidden="true"></i>Xóa</button>
                                        </fieldset>
                                        </form>
                                    @else
                                        <button type="button" class="product-delete-reason" data-toggle="popover" data-trigger="hover focus" data-placement="auto" data-container="body" data-content="{{ $deleteReason }}" aria-label="Lý do không thể xóa: {{ $deleteReason }}"><i class="fas fa-info-circle" aria-hidden="true"></i><span>Không thể xóa</span></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="product-empty-state"><i class="fas fa-search" aria-hidden="true"></i><strong>Không tìm thấy sản phẩm phù hợp.</strong><span>Thử thay đổi từ khóa, danh mục hoặc trạng thái.</span><a href="{{ route('admin.products.index') }}">Đặt lại bộ lọc</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$products" label="sản phẩm" />
    </div>
</section>
@endsection
@push('modals')
<div class="modal fade admin-product-preview" id="adminProductPreview" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="adminProductPreviewTitle">
    <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="adminProductPreviewTitle">Xem nhanh sản phẩm</h2><button type="button" class="close" data-dismiss="modal" aria-label="Đóng"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">
            <img data-preview-image alt="" hidden><p data-preview-no-image hidden>Chưa có ảnh sản phẩm.</p>
            <h3 class="h5" data-preview-name></h3>
            <dl class="product-preview-details">@foreach(['code' => 'Mã sản phẩm', 'price' => 'Giá bán', 'category' => 'Danh mục', 'stock' => 'Tồn kho', 'selling' => 'Kinh doanh', 'status' => 'Tình trạng'] as $key => $label)<div><dt>{{ $label }}</dt><dd data-preview-value="{{ $key }}"></dd></div>@endforeach</dl>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button></div>
    </div></div>
</div>
@endpush
@push('scripts')
<script src="{{ asset('js/admin-products.js') }}"></script>
@endpush
