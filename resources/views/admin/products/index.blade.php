@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@push('styles')
<style>
    /* Stop Selling Modal - Warm Amber Studio Refinement */
    :root {
        --stop-selling-amber-glow: rgba(245, 158, 11, 0.18);
        --stop-selling-amber-border: #fde68a;
        --stop-selling-amber-main: #f59e0b;
        --stop-selling-amber-deep: #d97706;
    }

    .stop-selling-modal-content {
        border-radius: 18px !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        background: #ffffff !important;
        box-shadow: 0 25px 40px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(245, 158, 11, 0.08) !important;
        animation: modalScaleUp 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @keyframes modalScaleUp {
        0% {
            opacity: 0;
            transform: scale(0.93) translateY(12px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .stop-selling-icon-badge {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 0 0 8px var(--stop-selling-amber-glow);
        animation: amberBadgePulse 2.4s infinite ease-in-out;
    }

    @keyframes amberBadgePulse {
        0%, 100% {
            box-shadow: 0 0 0 6px var(--stop-selling-amber-glow);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 0 14px rgba(245, 158, 11, 0.08);
            transform: scale(1.03);
        }
    }

    .status-shift-capsule {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .status-shift-capsule:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }

    .btn-stop-selling-action {
        background: linear-gradient(135deg, var(--stop-selling-amber-main) 0%, var(--stop-selling-amber-deep) 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        border: none !important;
        border-radius: 9px !important;
        padding: 9px 20px !important;
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.28) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-stop-selling-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(217, 119, 6, 0.38) !important;
        color: #ffffff !important;
    }

    .btn-stop-selling-action:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.25) !important;
    }

    .btn-stop-selling-cancel {
        border: 1px solid #cbd5e1 !important;
        border-radius: 9px !important;
        padding: 9px 20px !important;
        color: #475569 !important;
        background: #ffffff !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
    }

    .btn-stop-selling-cancel:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
        color: #1e293b !important;
    }

    .btn-open-stop-selling {
        transition: all 0.2s ease;
    }

    .btn-open-stop-selling:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25);
    }
</style>
@endpush

@section('content')
<h1 class="mb-4 text-center font-weight-bold" style="color: #333;">QUẢN LÝ SẢN PHẨM</h1>

<div class="mb-3 d-flex justify-content-between align-items-center">
    <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm sản phẩm..." />
        </div>
        <button type="submit" class="ml-2 btn btn-primary">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
        <a href="{{ route('admin.products.index') }}" class="ml-2 btn btn-outline-secondary">Đặt lại</a>
        @endif
    </form>
    <p class="mb-0 text-right">
        <a href="{{ route('admin.products.create') }}" class="btn btn-success">
            <i class="mr-1 fas fa-plus"></i> Tạo sản phẩm mới
        </a>
    </p>
</div>

<div class="p-0 border-0 shadow-sm table-container" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table mb-0 table-light table-striped table-hover">
            <thead>
                <tr class="thead-light">
                    <th>Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Giá Bán</th>
                    <th style="width: 90px;">Tồn kho</th>
                    <th>Phân loại</th>
                    <th style="min-width: 180px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $item)
                <tr @if($item->stock == 0) style="background-color: #ffebee; opacity: 0.7;" @endif>
                    <td>
                        @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-width: 90px; max-height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;" />
                        @else
                        <span class="badge badge-secondary">Không có ảnh</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        <div class="small text-muted">Mã: {{ $item->code }}</div>
                        @if ($item->stock == 0)
                        <span class="mt-1 badge badge-danger">Ngưng bán</span>
                        @endif
                    </td>
                    <td>
                        <span class=" text-dark">{{ number_format((float)$item->price, 0, ',', '.') }} VNĐ</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="{{ $item->stock == 0 ? 'text-danger font-weight-bold' : '' }}">
                            {{ $item->stock }}
                        </span>
                    </td>
                    <td>{{ data_get($item->category, 'name', 'Chưa phân loại') }}</td>
                    <td>
                        <a href="{{ route('admin.products.edit', $item) }}" class="mb-1 btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>

                        @if($item->stock == 0)
                            <button type="button" class="mb-1 btn btn-warning btn-sm" disabled title="Sản phẩm đã ngưng bán">
                                <i class="mr-1 fas fa-ban"></i> Ngưng bán
                            </button>
                        @else
                            <button type="button" class="mb-1 btn btn-warning btn-sm btn-open-stop-selling"
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-code="{{ $item->code }}"
                                data-stock="{{ $item->stock }}"
                                data-action="{{ route('admin.products.stop-selling', $item) }}">
                                <i class="mr-1 fas fa-ban"></i> Ngưng bán
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-muted">Không có sản phẩm nào phù hợp.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between align-items-center">
    <div class="text-muted">
        Trang {{ $products->currentPage() }} / {{ $products->lastPage() }} (Tổng: {{ $products->total() }} sản phẩm)
    </div>
    <nav aria-label="Page navigation">
        {{ $products->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection

@push('modals')
<!-- Modal Xác nhận Ngưng bán sản phẩm (Không dùng alert/confirm mặc định) -->
<div class="modal fade" id="stopSellingModal" tabindex="-1" role="dialog" aria-labelledby="stopSellingModalLabel" aria-hidden="true" style="backdrop-filter: blur(4px); background-color: rgba(15, 23, 42, 0.55);">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content stop-selling-modal-content">
            <div class="modal-body p-4 text-center">
                <!-- Icon badge cảnh báo nổi bật với hiệu ứng pulse -->
                <div class="d-flex justify-content-center mb-3 mt-1">
                    <span class="stop-selling-icon-badge">
                        <i class="fas fa-pause-circle"></i>
                    </span>
                </div>

                <!-- Tiêu đề -->
                <h4 class="font-weight-bold text-dark mb-1" id="stopSellingModalLabel" style="font-size: 20px; letter-spacing: -0.01em;">
                    Xác nhận ngưng bán sản phẩm
                </h4>

                <p class="text-muted small mb-3 px-2">
                    Bạn có chắc chắn muốn ngưng bán sản phẩm này? Thông tin tồn kho và tình trạng bán sẽ được cập nhật tức thì.
                </p>

                <!-- Status Shift Capsule (Trực quan hóa trạng thái trước & sau khi đổi) -->
                <div class="status-shift-capsule p-3 mb-4 text-left">
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-tag mr-1 text-secondary"></i> Tên sản phẩm:</span>
                        <span class="font-weight-bold text-dark text-truncate ml-2" id="modal-product-name" style="max-width: 220px;"></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small"><i class="fas fa-cubes mr-1 text-secondary"></i> Số lượng tồn:</span>
                        <div class="d-flex align-items-center font-weight-bold">
                            <span class="badge badge-secondary px-2 py-1" id="modal-current-stock">0</span>
                            <i class="fas fa-arrow-right mx-2 text-warning" style="font-size: 10px;"></i>
                            <span class="badge badge-danger px-2 py-1">0</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small"><i class="fas fa-toggle-on mr-1 text-secondary"></i> Tình trạng bán:</span>
                        <div class="d-flex align-items-center font-weight-bold">
                            <span class="badge badge-success px-2 py-1">Còn hàng</span>
                            <i class="fas fa-arrow-right mx-2 text-warning" style="font-size: 10px;"></i>
                            <span class="badge badge-dark px-2 py-1">Hết hàng</span>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="d-flex align-items-center justify-content-center" style="gap: 12px;">
                    <button type="button" class="btn btn-stop-selling-cancel" data-dismiss="modal">
                        Hủy bỏ
                    </button>
                    <form id="stop-selling-form" method="POST" action="" style="display: inline-block; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-stop-selling-action">
                            <i class="fas fa-ban mr-1"></i> Xác nhận ngưng bán
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        // Mở popup xác nhận ngưng bán khi click nút
        $(document).on('click', '.btn-open-stop-selling', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name');
            var stock = $btn.data('stock');

            $('#modal-product-name').text(name || 'Sản phẩm');
            $('#modal-current-stock').text(stock !== undefined ? stock : 0);
            $('#stop-selling-form').attr('action', action);

            $('#stopSellingModal').modal('show');
        });
    });
</script>
@endpush
