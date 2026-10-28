@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code . ' - ' . config('app.name', 'Shop Hoa Tươi'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb_style.css') }}">
    <style>
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
        .order-step-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 20px 0 35px;
        }
        .order-step-timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 40px;
            right: 40px;
            height: 3px;
            background: #e5e7eb;
            z-index: 1;
        }
        .order-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        .step-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #f3f4f6;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            font-size: 16px;
        }
        .order-step.completed .step-icon {
            background: #537453;
            color: #fff;
        }
        .order-step.active .step-icon {
            background: #ea8725;
            color: #fff;
        }
        .step-label {
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
        }
        .order-step.completed .step-label {
            color: #537453;
        }
        .order-step.active .step-label {
            color: #ea8725;
        }
        .info-box {
            background: #fbfcfb;
            border: 1px solid #ebf2eb;
            border-radius: 8px;
            padding: 18px 20px;
            height: 100%;
        }
        .info-box h5 {
            font-size: 15px;
            font-weight: 700;
            color: #537453;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2ece2;
        }
        @media print {
            header, footer, .breadcrumb_background, .col-left-ac, .no-print, #loading-overlay {
                display: none !important;
            }
            .col-right-ac {
                width: 100% !important;
            }
            .account-card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="py-2 mb-3 breadcrumb_background bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="py-2 mb-0 breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none text-muted">Đơn hàng của bạn</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Chi tiết hóa đơn #{{ $order->order_code }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="page_customer_account">
    <div class="container">
        <div class="row">
            <!-- Cột trái: Điều hướng tài khoản -->
            <aside class="col-xs-12 col-sm-12 col-lg-3 col-left-ac mb-4 no-print">
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

            <!-- Cột phải: Chi tiết đơn hàng / Hóa đơn -->
            <main class="col-xs-12 col-sm-12 col-lg-9 col-right-ac">
                <!-- Thanh công cụ trên đầu -->
                <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                        <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách đơn hàng
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-outline-success btn-sm" style="border-radius: 8px;">
                        <i class="fa fa-print me-1"></i> In hóa đơn
                    </button>
                </div>

                <div class="account-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h4>
                                <i class="fa fa-file-invoice"></i> HÓA ĐƠN CHI TIẾT #{{ $order->order_code }}
                            </h4>
                            <div class="small text-muted mt-1">
                                Ngày đặt (UTC+7): {{ $order->order_date?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') ?? ($order->created_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') ?? '—') }}
                            </div>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            @if($order->status === 'Pending')
                                <span class="badge bg-warning text-dark px-3 py-2" style="font-size: 13px; border-radius: 6px;">
                                    <i class="fa fa-clock me-1"></i>Trạng thái: Chờ xử lý
                                </span>
                            @elseif($order->status === 'Processing')
                                <span class="badge bg-info text-dark px-3 py-2" style="font-size: 13px; border-radius: 6px;">
                                    <i class="fa fa-cog me-1"></i>Trạng thái: Đang chuẩn bị hàng
                                </span>
                            @elseif($order->status === 'Shipped')
                                <span class="badge bg-primary px-3 py-2" style="font-size: 13px; border-radius: 6px;">
                                    <i class="fa fa-truck me-1"></i>Trạng thái: Đang giao hàng
                                </span>
                            @elseif($order->status === 'Completed')
                                <span class="badge bg-success px-3 py-2" style="font-size: 13px; border-radius: 6px;">
                                    <i class="fa fa-check-circle me-1"></i>Trạng thái: Đã hoàn tất
                                </span>
                            @elseif($order->status === 'Cancelled')
                                <span class="badge bg-danger px-3 py-2" style="font-size: 13px; border-radius: 6px;">
                                    <i class="fa fa-times-circle me-1"></i>Trạng thái: Đã hủy
                                </span>
                            @else
                                <span class="badge bg-secondary px-3 py-2" style="font-size: 13px; border-radius: 6px;">
                                    Trạng thái: {{ $order->status }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Tiến trình đơn hàng -->
                        @if($order->status !== 'Cancelled')
                            <div class="order-step-timeline no-print">
                                <div class="order-step completed">
                                    <div class="step-icon"><i class="fa fa-cart-shopping"></i></div>
                                    <div class="step-label">Đặt hàng</div>
                                </div>
                                <div class="order-step {{ in_array($order->status, ['Processing', 'Shipped', 'Completed']) ? 'completed' : ($order->status === 'Pending' ? 'active' : '') }}">
                                    <div class="step-icon"><i class="fa fa-receipt"></i></div>
                                    <div class="step-label">Xác nhận</div>
                                </div>
                                <div class="order-step {{ in_array($order->status, ['Shipped', 'Completed']) ? 'completed' : ($order->status === 'Processing' ? 'active' : '') }}">
                                    <div class="step-icon"><i class="fa fa-box-open"></i></div>
                                    <div class="step-label">Đóng gói</div>
                                </div>
                                <div class="order-step {{ $order->status === 'Completed' ? 'completed' : ($order->status === 'Shipped' ? 'active' : '') }}">
                                    <div class="step-icon"><i class="fa fa-truck"></i></div>
                                    <div class="step-label">Giao hàng</div>
                                </div>
                                <div class="order-step {{ $order->status === 'Completed' ? 'completed' : '' }}">
                                    <div class="step-icon"><i class="fa fa-circle-check"></i></div>
                                    <div class="step-label">Hoàn tất</div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger mb-4">
                                <i class="fa fa-exclamation-triangle me-2"></i>Đơn hàng này đã bị hủy.
                            </div>
                        @endif

                        <!-- Thông tin người nhận & Thanh toán -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h5><i class="fa fa-map-marker-alt me-2"></i>Thông tin người nhận</h5>
                                    <p class="mb-1"><strong>Người nhận:</strong> {{ $order->customer['name'] ?? '—' }} — {{ $order->customer['phone'] ?? '—' }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $order->customer['email'] ?? '—' }}</p>
                                    <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->customer['address'] ?? '—' }}</p>
                                    <p class="mb-0"><strong>Ghi chú:</strong> {{ $order->customer['note'] ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h5><i class="fa fa-credit-card me-2"></i>Thanh toán & Giao nhận</h5>
                                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> {{ $order->payment_method ?? 'COD' }}</p>
                                    <p class="mb-1"><strong>Thanh toán:</strong> {{ $order->payment_method ?? 'COD' }}</p>
                                    <p class="mb-1"><strong>Thời gian giao dự kiến:</strong> 2 giờ kể từ khi xác nhận</p>
                                    <p class="mb-0"><strong>Phí vận chuyển:</strong> <span class="text-success fw-bold">Miễn phí</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">
                                <i class="fa fa-basket-shopping me-2 text-success"></i>Sản phẩm đã đặt
                            </h5>
                            <div class="table-responsive border rounded">
                                <table class="table align-middle mb-0">
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
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $item['product_name'] }}</div>
                                                    @if(!empty($item['product_code']))
                                                        <small class="text-muted">Mã: {{ $item['product_code'] }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) (string) $item['price'], 0, ',', '.') }} đ
                                                </td>
                                                <td class="text-center">
                                                    {{ $item['quantity'] }}
                                                </td>
                                                <td class="text-end fw-semibold text-dark">
                                                    {{ number_format((float) (string) $item['subtotal'], 0, ',', '.') }} đ
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end text-muted">Tạm tính:</td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end text-muted">Phí giao hàng:</td>
                                            <td class="text-end text-success fw-semibold">Miễn phí</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold fs-6">Tổng tiền:</td>
                                            <td class="text-end fw-bold text-danger fs-5">
                                                {{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Chân trang hóa đơn / Liên hệ hỗ trợ -->
                        <div class="p-3 bg-light rounded text-center small text-muted">
                            Nếu bạn có bất kỳ thắc mắc nào về hóa đơn này, vui lòng liên hệ với Góc Hoa Xinh qua hotline: <strong>090.123.4567</strong> hoặc email: <strong>cskh@shophoatuoi.com</strong>.
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>
@endsection
