@extends('layouts.admin')

@section('title', 'Quản lý Hóa Đơn')

@push('styles')
<style>
    /* Complete Order Modal - Emerald Atelier Refinement */
    :root {
        --complete-order-emerald-glow: rgba(16, 185, 129, 0.22);
        --complete-order-emerald-border: #a7f3d0;
        --complete-order-emerald-main: #10b981;
        --complete-order-emerald-deep: #059669;
    }

    .complete-order-modal-content {
        border-radius: 20px !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        background: #ffffff !important;
        box-shadow: 0 25px 45px -10px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(16, 185, 129, 0.08) !important;
        animation: completeModalScaleUp 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @keyframes completeModalScaleUp {
        0% {
            opacity: 0;
            transform: scale(0.93) translateY(12px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .complete-order-icon-badge {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 0 0 8px var(--complete-order-emerald-glow);
        animation: emeraldBadgePulse 2.4s infinite ease-in-out;
    }

    @keyframes emeraldBadgePulse {
        0%, 100% {
            box-shadow: 0 0 0 6px var(--complete-order-emerald-glow);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 0 14px rgba(16, 185, 129, 0.1);
            transform: scale(1.03);
        }
    }

    .order-settlement-capsule {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .order-settlement-capsule:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }

    .btn-order-complete-action {
        background: linear-gradient(135deg, var(--complete-order-emerald-main) 0%, var(--complete-order-emerald-deep) 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 10px 22px !important;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.32) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-order-complete-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.42) !important;
        color: #ffffff !important;
    }

    .btn-order-complete-action:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25) !important;
    }

    .btn-order-complete-cancel {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 10px 20px !important;
        color: #475569 !important;
        background: #ffffff !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
    }

    .btn-order-complete-cancel:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
        color: #1e293b !important;
    }

    .btn-open-complete-order {
        transition: all 0.2s ease;
    }

    .btn-open-complete-order:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
    }
</style>
@endpush

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ HÓA ĐƠN</h1>

<form action="{{ route('admin.orders.index') }}" method="GET" class="form-inline mb-3">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
    <div class="form-group">
        <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm theo tên hoặc tài khoản..." />
    </div>
    <div class="form-group ml-2">
        <select name="status" class="form-control" onchange="this.form.submit()">
            <option value="">-- Tất cả trạng thái --</option>
            @foreach(\App\Models\Order::STATUSES as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ \App\Models\Order::STATUS_LABELS[$st] ?? $st }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
    @if(request('searchString') || request('q') || request('status'))
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
    @endif
</form>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th style="width: 100px; max-width: 100px;">Mã HĐ</th>
                    <th>Tên Người Nhận</th>
                    <th>Tài Khoản</th>
                    <th>Địa Chỉ</th>
                    <th>Ngày Đặt</th>
                    <th>Trạng Thái</th>
                    <th>Tổng Tiền</th>
                    <th style="min-width: 170px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $item)
                    <tr>
                        <td style="width: 100px; max-width: 100px;">
                            <strong class="text-info d-inline-block text-truncate" style="max-width: 85px; vertical-align: middle;" title="{{ $item->order_code }}">
                                {{ $item->order_code }}
                            </strong>
                        </td>
                        <td>{{ $item->customer['name'] ?? '—' }}</td>
                        <td>{{ $item->user->name ?? ($item->customer['email'] ?? 'Khách vãng lai') }}</td>
                        <td>
                            <span data-toggle="tooltip" data-placement="top" title="{{ $item->customer['address'] ?? '' }}" style="cursor: help;">
                                {{ Str::limit($item->customer['address'] ?? '—', 30) }}
                            </span>
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if ($item->status == 'Pending')
                                <span class="status-pending">Chờ xử lý</span>
                            @elseif ($item->status == 'Processing')
                                <span class="status-processing">Đang đóng gói</span>
                            @elseif ($item->status == 'Shipped')
                                <span class="status-shipped">Đang giao hàng</span>
                            @elseif ($item->status == 'Completed')
                                <span class="status-completed">Hoàn thành</span>
                            @elseif ($item->status == 'Cancelled')
                                <span class="status-cancelled">Đã hủy</span>
                            @else
                                <span class="badge badge-secondary">{{ $item->status_label ?? $item->status }}</span>
                            @endif
                        </td>
                        <td><strong>{{ number_format((float) (string) ($item->total_amount ?? $item->total), 0, ',', '.') }} đ</strong></td>
                        <td>
                            <a href="{{ route('admin.orders.show', $item->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>

                            @if ($item->status != 'Completed' && $item->status != 'Cancelled')
                                <button type="button" 
                                        class="btn btn-success btn-sm ml-1 btn-open-complete-order"
                                        data-action="{{ route('admin.orders.complete', $item->id) }}"
                                        data-code="{{ $item->order_code }}"
                                        data-customer="{{ $item->customer['name'] ?? ($item->user->name ?? 'Khách vãng lai') }}"
                                        data-amount="{{ number_format((float) (string) ($item->total_amount ?? $item->total), 0, ',', '.') }} đ"
                                        data-status="{{ \App\Models\Order::STATUS_LABELS[$item->status] ?? $item->status }}"
                                        title="Xác nhận hoàn thành hóa đơn">
                                    <i class="fas fa-check mr-1"></i>Hoàn thành
                                </button>
                            @elseif ($item->status == 'Completed')
                                <span class="text-success font-weight-bold ml-1 small">✓ Hoàn thành</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy đơn hàng nào phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-admin.pagination :paginator="$orders" label="hóa đơn" />
@endsection

@push('modals')
<!-- Modal Xác nhận Hoàn thành Hóa đơn -->
<div class="modal fade" id="completeOrderModal" tabindex="-1" role="dialog" aria-labelledby="completeOrderModalLabel" aria-hidden="true" style="backdrop-filter: blur(5px); background-color: rgba(15, 23, 42, 0.55);">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
        <div class="modal-content complete-order-modal-content">
            <div class="modal-body p-4 text-center">
                <!-- Icon badge hoàn thành với hiệu ứng pulse ngọc bích -->
                <div class="d-flex justify-content-center mb-3 mt-1">
                    <span class="complete-order-icon-badge">
                        <i class="fas fa-check-double"></i>
                    </span>
                </div>

                <!-- Tiêu đề -->
                <h4 class="font-weight-bold text-dark mb-1" id="completeOrderModalLabel" style="font-size: 21px; letter-spacing: -0.01em;">
                    Xác nhận hoàn thành hóa đơn
                </h4>

                <p class="text-muted small mb-3 px-2">
                    Xác nhận đơn hàng đã thanh toán thành công và khách hàng đã nhận được hoa tươi?
                </p>

                <!-- Order Settlement Card: Trực quan hóa chi tiết đơn hàng & trạng thái chuyển tiếp -->
                <div class="order-settlement-capsule p-3 mb-4 text-left">
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-receipt mr-1 text-secondary"></i> Mã hóa đơn:</span>
                        <span class="font-weight-bold text-info" id="modal-order-code">#—</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-user mr-1 text-secondary"></i> Người nhận:</span>
                        <span class="font-weight-bold text-dark text-truncate ml-2" id="modal-order-customer" style="max-width: 220px;">—</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-money-bill-wave mr-1 text-secondary"></i> Tổng tiền:</span>
                        <span class="font-weight-bold text-success font-size-15" id="modal-order-amount">0 đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small"><i class="fas fa-exchange-alt mr-1 text-secondary"></i> Trạng thái:</span>
                        <div class="d-flex align-items-center font-weight-bold">
                            <span class="badge badge-warning px-2 py-1 text-dark" id="modal-order-current-status">Đang xử lý</span>
                            <i class="fas fa-arrow-right mx-2 text-muted" style="font-size: 11px;"></i>
                            <span class="badge badge-success px-2 py-1" style="background-color: #10b981;"><i class="fas fa-check mr-1"></i>Hoàn thành</span>
                        </div>
                    </div>
                </div>

                <!-- Nút thao tác -->
                <div class="d-flex align-items-center justify-content-center" style="gap: 12px;">
                    <button type="button" class="btn btn-order-complete-cancel" data-dismiss="modal">
                        Hủy bỏ
                    </button>
                    <form id="complete-order-form" method="POST" action="" style="display: inline-block; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-order-complete-action">
                            <i class="fas fa-check-circle mr-1"></i> Xác nhận hoàn thành
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
        // Mở popup xác nhận hoàn thành khi click nút "Hoàn thành"
        $(document).on('click', '.btn-open-complete-order', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            var code = $btn.data('code');
            var customer = $btn.data('customer');
            var amount = $btn.data('amount');
            var status = $btn.data('status');

            $('#modal-order-code').text(code ? '#' + code : '#—');
            $('#modal-order-customer').text(customer || 'Khách vãng lai');
            $('#modal-order-amount').text(amount || '0 đ');
            $('#modal-order-current-status').text(status || 'Đang xử lý');
            $('#complete-order-form').attr('action', action);

            $('#completeOrderModal').modal('show');
        });
    });
</script>
@endpush
