@extends('layouts.app')

@section('title', 'Đơn hàng của bạn - ' . config('app.name', 'Shop Hoa Tươi'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb_style.css') }}">
    <style>
        .modal {
            z-index: 1065 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .page_customer_account {
            padding: 30px 0 60px;
            background: #fbfbfb;
        }
        .col-left-ac .block-account {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .col-left-ac .block-account .title-account {
            font-size: 18px;
            font-weight: 700;
            color: #537453;
            text-transform: uppercase;
            margin-bottom: 8px;
            padding-bottom: 10px;
            border-bottom: 2px solid #537453;
        }
        .col-left-ac .block-account p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        .col-left-ac .block-account ul {
            padding-left: 0;
            list-style: none;
            margin-bottom: 0;
        }
        .col-left-ac .block-account ul li {
            margin-bottom: 10px;
        }
        .col-left-ac .block-account ul .title-info {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            border-radius: 6px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
        }
        .col-left-ac .block-account ul .title-info:hover,
        .col-left-ac .block-account ul .title-info.active {
            background-color: #f0f5f0;
            color: #537453;
            font-weight: 600;
        }
        .col-left-ac .block-account ul .title-info i {
            width: 22px;
            font-size: 15px;
        }
        .account-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .account-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 18px 24px;
        }
        .account-card .card-header h4 {
            font-size: 17px;
            font-weight: 700;
            color: #537453;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .account-card .card-body {
            padding: 24px;
        }
        .stat-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #eee;
            padding: 16px 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stat-icon.all { background: #eef6ee; color: #537453; }
        .stat-icon.pending { background: #fff8e6; color: #d97706; }
        .stat-icon.processing { background: #eff6ff; color: #2563eb; }
        .stat-icon.completed { background: #ecfdf5; color: #059669; }
        .stat-info .num {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
        }
        .stat-info .label {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 0;
        }
        .table-orders {
            margin-bottom: 0;
        }
        .table-orders thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 14px 16px;
            border-bottom: 2px solid #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
        }
        .table-orders tbody td {
            padding: 16px;
            font-size: 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        .table-orders tbody tr:hover {
            background-color: #fafcfb;
        }
        .order-code-badge {
            font-family: monospace;
            font-size: 13px;
            font-weight: 700;
            color: #537453;
            text-decoration: none;
            background: #f0f5f0;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .order-code-badge:hover {
            background: #537453;
            color: #fff;
        }
        .btn-style {
            background-color: #537453 !important;
            color: #fff !important;
            border: none;
            padding: 10px 24px;
            font-size: 14px !important;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }
        .btn-style:hover {
            background-color: #ea8725 !important;
            color: #fff !important;
        }
    </style>
@endpush

@section('content')
<div class="py-2 mb-3 breadcrumb_background bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="py-2 mb-0 breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}" class="text-decoration-none text-muted">Trang tài khoản</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Đơn hàng của bạn</li>
            </ol>
        </nav>
    </div>
</div>

<section class="page_customer_account">
    <div class="container">
        <div class="row">
            <!-- Cột trái: Điều hướng tài khoản -->
            <aside class="col-xs-12 col-sm-12 col-lg-3 col-left-ac mb-4">
                <div class="block-account">
                    <h5 class="title-account">Trang tài khoản</h5>
                    <p>
                        Xin chào, <span style="color:#01567f; font-weight: 600;">{{ auth()->user()->name ?: auth()->user()->username }}</span>!
                    </p>
                    <ul>
                        <li>
                            <a class="title-info" href="{{ route('profile.edit') }}">
                                <i class="fa fa-user me-2 text-muted"></i>Thông tin tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="title-info active" href="{{ route('orders.index') }}">
                                <i class="fa fa-shopping-bag me-2" style="color: #537453;"></i>Đơn hàng của bạn
                            </a>
                        </li>
                        <li>
                            <a class="title-info" href="/wishlist">
                                <i class="fa fa-heart me-2 text-danger"></i>Danh sách yêu thích
                            </a>
                        </li>
                        <li>
                            <a class="title-info" href="{{ route('profile.edit') }}#doi-mat-khau">
                                <i class="fa fa-key me-2 text-muted"></i>Đổi mật khẩu
                            </a>
                        </li>
                        <li>
                            <form method="post" action="{{ route('logout') }}" id="sidebarLogoutForm" style="display:inline;">
                                @csrf
                                <a class="title-info text-danger" href="#" onclick="document.getElementById('sidebarLogoutForm').submit(); return false;">
                                    <i class="fa fa-sign-out me-2"></i>Đăng xuất
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Cột phải: Danh sách hóa đơn / đơn hàng -->
            <main class="col-xs-12 col-sm-12 col-lg-9 col-right-ac">
                @php
                    $salesService = app(\App\Services\SalesService::class);
                    $allOrdersQuery = $salesService->owned(\App\Models\Order::query(), auth()->user());
                    $totalCount = (clone $allOrdersQuery)->count();
                    $pendingCount = (clone $allOrdersQuery)->where('status', 'Pending')->count();
                    $processingCount = (clone $allOrdersQuery)->whereIn('status', ['Processing', 'Shipped'])->count();
                    $completedCount = (clone $allOrdersQuery)->where('status', 'Completed')->count();
                @endphp

                <!-- Thống kê nhanh hóa đơn -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon all"><i class="fa fa-receipt"></i></div>
                            <div class="stat-info">
                                <div class="num">{{ $totalCount }}</div>
                                <div class="label">Tổng hóa đơn</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon pending"><i class="fa fa-clock"></i></div>
                            <div class="stat-info">
                                <div class="num">{{ $pendingCount }}</div>
                                <div class="label">Chờ xác nhận</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon processing"><i class="fa fa-truck-fast"></i></div>
                            <div class="stat-info">
                                <div class="num">{{ $processingCount }}</div>
                                <div class="label">Đang giao</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon completed"><i class="fa fa-circle-check"></i></div>
                            <div class="stat-info">
                                <div class="num">{{ $completedCount }}</div>
                                <div class="label">Đã hoàn tất</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách hóa đơn -->
                <div class="account-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>
                            <i class="fa fa-file-invoice-dollar"></i> Danh sách hóa đơn mua hàng
                        </h4>
                        <span class="badge bg-light text-dark border">
                            {{ $orders->total() }} hóa đơn
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if ($orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-orders align-middle">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Ngày đặt</th>
                                            <th>Người nhận</th>
                                            <th>Sản phẩm</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                            <tr>
                                                <!-- Mã đơn hàng -->
                                                <td>
                                                    <a href="{{ route('orders.show', $order->id) }}" class="order-code-badge" title="Xem hóa đơn {{ $order->order_code }}">
                                                        #{{ $order->order_code }}
                                                    </a>
                                                </td>

                                                <!-- Ngày đặt -->
                                                <td>
                                                    <div class="fw-semibold text-dark">
                                                        {{ $order->order_date ? $order->order_date->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') : ($order->created_at ? $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') : '—') }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $order->order_date ? $order->order_date->timezone('Asia/Ho_Chi_Minh')->format('H:i') : ($order->created_at ? $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i') : '') }}
                                                    </small>
                                                </td>

                                                <!-- Người nhận & Địa chỉ -->
                                                <td>
                                                    <div class="fw-semibold text-dark">
                                                        {{ $order->customer['name'] ?? '—' }}
                                                    </div>
                                                    @if (!empty($order->customer['phone']))
                                                        <div class="small text-muted">
                                                            <i class="fa fa-phone me-1 text-secondary"></i>{{ $order->customer['phone'] }}
                                                        </div>
                                                    @endif
                                                    @if (!empty($order->customer['address']))
                                                        <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $order->customer['address'] }}">
                                                            <i class="fa fa-map-marker-alt me-1 text-danger"></i>{{ $order->customer['address'] }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <!-- Tóm tắt sản phẩm -->
                                                <td>
                                                    <div class="small">
                                                        @foreach(array_slice($order->items ?? [], 0, 2) as $item)
                                                            <div class="text-truncate mb-1" style="max-width: 200px;" title="{{ $item['product_name'] ?? '' }}">
                                                                <span class="text-dark fw-500">• {{ $item['product_name'] ?? 'Sản phẩm' }}</span>
                                                                <span class="text-muted">(x{{ $item['quantity'] ?? 1 }})</span>
                                                            </div>
                                                        @endforeach
                                                        @if(count($order->items ?? []) > 2)
                                                            <div class="text-muted fst-italic">
                                                                + {{ count($order->items) - 2 }} sản phẩm khác
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- Tổng tiền & Phương thức thanh toán -->
                                                <td>
                                                    <div class="fw-bold text-danger">
                                                        {{ number_format((float) (string) ($order->total_amount ?? $order->total), 0, ',', '.') }} đ
                                                    </div>
                                                    <div class="small text-muted mt-1">
                                                        <i class="fa fa-credit-card me-1"></i>{{ $order->payment_method ?? 'COD' }}
                                                    </div>
                                                </td>

                                                <!-- Trạng thái -->
                                                <td>
                                                    @if($order->status === 'Pending')
                                                        <span class="badge bg-warning text-dark px-2 py-1" style="border-radius: 6px;">
                                                            <i class="fa fa-clock me-1"></i>Chờ xử lý
                                                        </span>
                                                    @elseif($order->status === 'Processing')
                                                        <span class="badge bg-info text-dark px-2 py-1" style="border-radius: 6px;">
                                                            <i class="fa fa-box-open me-1"></i>Đang đóng gói
                                                        </span>
                                                    @elseif($order->status === 'Shipped')
                                                        <span class="badge bg-primary px-2 py-1" style="border-radius: 6px;">
                                                            <i class="fa fa-truck me-1"></i>Đang giao
                                                        </span>
                                                    @elseif($order->status === 'Completed')
                                                        <span class="badge bg-success px-2 py-1" style="border-radius: 6px;">
                                                            <i class="fa fa-check-circle me-1"></i>Hoàn tất
                                                        </span>
                                                    @elseif($order->status === 'Cancelled')
                                                        <span class="badge bg-danger px-2 py-1" style="border-radius: 6px;">
                                                            <i class="fa fa-times-circle me-1"></i>Đã hủy
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary px-2 py-1" style="border-radius: 6px;">
                                                            {{ $order->status }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Nút thao tác -->
                                                <td class="text-center" style="white-space: nowrap;">
                                                    <a href="{{ route('orders.show', $order->id) }}"
                                                       class="btn btn-outline-success btn-sm me-1"
                                                       style="font-size: small; border-radius: 8px;"
                                                       title="Xem chi tiết hóa đơn">
                                                        <i class="fa fa-eye me-1"></i>Chi tiết
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#orderQuickViewModal_{{ $order->id }}"
                                                            style="font-size: small; border-radius: 8px;"
                                                            title="Xem nhanh">
                                                        <i class="fa fa-receipt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Phân trang -->
                            @if ($orders->hasPages())
                                <div class="p-3 border-top d-flex justify-content-center">
                                    {{ $orders->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fa fa-receipt text-muted" style="font-size: 54px; opacity: 0.35;"></i>
                                </div>
                                <h5 class="text-dark fw-bold mb-2">Bạn chưa có đơn hàng nào</h5>
                                <p class="text-muted mb-4 small">Khi bạn đặt mua hoa, thông tin đơn hàng và hóa đơn sẽ xuất hiện tại đây để bạn tiện theo dõi.</p>
                                <a href="{{ route('home') }}" class="btn btn-style">
                                    <i class="fa fa-shopping-basket me-1"></i> Khám phá hoa tươi ngay
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>
@endsection

@push('modals')
<!-- Quick View Modals (Rendered outside .bodywrap to prevent backdrop trapping) -->
@foreach ($orders as $order)
    <div class="modal fade" id="orderQuickViewModal_{{ $order->id }}" tabindex="-1" aria-labelledby="modalLabel_{{ $order->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-success" id="modalLabel_{{ $order->id }}">
                        <i class="fa fa-file-invoice me-2"></i>Hóa đơn #{{ $order->order_code }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <p class="mb-1 text-muted small">Mã đơn hàng:</p>
                            <p class="fw-bold mb-2">#{{ $order->order_code }}</p>

                            <p class="mb-1 text-muted small">Thời gian đặt:</p>
                            <p class="fw-semibold mb-2">
                                {{ $order->order_date ? $order->order_date->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') : ($order->created_at ? $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') : '—') }}
                            </p>

                            <p class="mb-1 text-muted small">Phương thức thanh toán:</p>
                            <p class="fw-semibold mb-0">{{ $order->payment_method ?? 'COD' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <p class="mb-1 text-muted small">Người nhận hàng:</p>
                            <p class="fw-bold mb-2">{{ $order->customer['name'] ?? '—' }} ({{ $order->customer['phone'] ?? '—' }})</p>

                            <p class="mb-1 text-muted small">Địa chỉ nhận hàng:</p>
                            <p class="mb-2 text-dark">{{ $order->customer['address'] ?? '—' }}</p>

                            @if(!empty($order->customer['note']))
                                <p class="mb-1 text-muted small">Ghi chú giao hàng:</p>
                                <p class="mb-0 text-muted fst-italic">{{ $order->customer['note'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['product_name'] ?? 'Sản phẩm' }}</td>
                                        <td class="text-end">{{ number_format((float) (string) ($item['price'] ?? 0), 0, ',', '.') }} đ</td>
                                        <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="text-end fw-semibold">{{ number_format((float) (string) ($item['subtotal'] ?? 0), 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng thanh toán:</td>
                                    <td class="text-end fw-bold text-danger">
                                        {{ number_format((float) (string) ($order->total_amount ?? $order->total), 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-style w-100 py-2 fw-bold text-center text-uppercase" style="border-radius: 8px;">
                        <i class="fa fa-external-link-alt me-2"></i> Xem chi tiết đầy đủ
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endpush
