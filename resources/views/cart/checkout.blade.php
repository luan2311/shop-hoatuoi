<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#537547" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thanh toán - Blossom Corner | Shop Hoa Tươi Góc Hoa Xinh</title>

    <!-- Favicon -->
    <link rel="icon" href="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/favicon.png?1757405855081" type="image/x-icon">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Checkout Stylesheet (Ported & Elevated from C# ASP MVC 5 Shop-Flower) -->
    <link rel="stylesheet" href="{{ asset('css/checkout_style.css') }}?v={{ @filemtime(public_path('css/checkout_style.css')) ?: 1 }}" />
    <script src="{{ asset('js/checkout-voucher.js') }}" defer></script>
</head>

<body class="checkout-body">
    <div class="checkout-page-wrapper">

        <!-- Minimalist Editorial Topbar -->
        <header class="checkout-topbar">
            <div class="container">
                <a href="{{ route('home') }}" class="topbar-brand" title="Quay về Trang chủ Góc Hoa Xinh">
                    <img src="{{ asset('images/checkout_logo.jpg') }}" alt="Blossom Corner" class="logo-img" onerror="this.onerror=null;this.src='{{ asset('images/cate_1.png') }}';" />
                    <div class="brand-text">
                        <span class="brand-name">Góc Hoa Xinh</span>
                        <span class="brand-tagline">Blossom Corner &bull; Tiệm Hoa Tươi</span>
                    </div>
                </a>

                <div class="topbar-secure-badges">
                    <!-- <div class="secure-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Thanh toán bảo mật 256-bit SSL</span>
                    </div> -->
                    <div class="topbar-hotline">
                        Hỗ trợ đơn hoa: <a href="tel:0898449950">0898.449.950</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Order Summary Drawer Toggle -->
        <div class="mobile-summary-toggle" id="mobileSummaryToggle">
            <div class="mobile-summary-inner">
                <div class="mobile-toggle-btn" id="mobileToggleBtn">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Xem tóm tắt đơn hàng</span>
                    <i class="fa-solid fa-chevron-down ms-1" id="mobileToggleIcon"></i>
                </div>
                <div class="mobile-total-preview" id="mobileTotalPreview">
                    {{ number_format($total, 0, ',', '.') }} đ
                </div>
            </div>
        </div>

        <!-- Main Checkout Workspace -->
        <main class="checkout-main-wrap">
            <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ old('checkout_token', $checkoutToken) }}">
                <input type="hidden" name="voucher_code" id="appliedVoucher" value="{{ $voucher_code }}">

                <div class="checkout-container">

                    <!-- LEFT COLUMN: Information & Payment (Main Content) -->
                    <div class="main-content">

                        <!-- Breadcrumb Steps Navigation -->
                        <nav class="checkout-steps" aria-label="Các bước đặt hàng">
                            <a href="{{ route('cart.index') }}" class="step-item completed">
                                <i class="fa-solid fa-check-circle step-icon"></i>
                                <span>Giỏ hàng</span>
                            </a>
                            <span class="step-separator"><i class="fa-solid fa-chevron-right"></i></span>
                            <span class="step-item active">
                                <i class="fa-solid fa-circle-dot step-icon"></i>
                                <span>Thông tin nhận hàng & Thanh toán</span>
                            </span>
                            <span class="step-separator"><i class="fa-solid fa-chevron-right"></i></span>
                            <span class="step-item">
                                <i class="fa-regular fa-circle step-icon"></i>
                                <span>Hoàn tất</span>
                            </span>
                        </nav>

                        <!-- Logged-in Customer Badge -->
                        @if(auth()->check())
                        <div class="checkout-user-badge">
                            <div class="user-badge-left">
                                <div class="user-avatar-placeholder">
                                    {{ mb_substr(auth()->user()->name ?? 'K', 0, 1, 'UTF-8') }}
                                </div>
                                <div>
                                    <span class="user-meta-name">{{ auth()->user()->name }}</span>
                                    <span class="user-meta-email">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                            <div class="user-badge-right">
                                <a href="{{ route('orders.index') }}" target="_blank" title="Xem danh sách đơn hàng đã mua">
                                    <i class="fa-solid fa-receipt"></i> Đơn hàng của tôi
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Backend Alert Messages -->
                        @if($errors->any())
                        <div class="p-3 mb-4 rounded" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13.5px;">
                            <div class="mb-1 fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> Có lỗi xảy ra trong quá trình đặt hàng:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- SECTION 1: Thông tin người nhận -->
                        <section class="form-section">
                            <div class="form-section-header">
                                <h3><span class="section-num">1</span> Thông tin nhận hàng</h3>
                            </div>
                            <p class="form-section-desc">Vui lòng cung cấp địa chỉ và số điện thoại chính xác để tiệm hoa giao hàng đúng giờ.</p>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="form-label" for="Email">Địa chỉ Email</label>
                                <div class="input-with-icon">
                                    <input type="email" class="form-control" id="Email" name="email"
                                        placeholder="nguyenvana@gmail.com"
                                        value="{{ old('email', auth()->user()->email ?? '') }}"
                                        maxlength="255" autocomplete="email">
                                    <i class="fa-regular fa-envelope input-icon"></i>
                                </div>
                                <span class="field-validation-valid" data-valmsg-for="Email"></span>
                                <span class="field-validation-valid" data-valmsg-for="email"></span>
                            </div>

                            <!-- Họ và tên người nhận -->
                            <div class="form-group">
                                <label class="form-label" for="HoTenNguoiNhan">Họ và tên người nhận <span class="required-mark">*</span></label>
                                <div class="input-with-icon">
                                    <input type="text" class="form-control" id="HoTenNguoiNhan" name="receiver_name"
                                        placeholder="Nhập họ và tên người nhận hoa"
                                        value="{{ old('receiver_name', auth()->user()->name ?? '') }}"
                                        maxlength="100" required autocomplete="name">
                                    <i class="fa-regular fa-user input-icon"></i>
                                </div>
                                <span class="field-validation-valid" data-valmsg-for="HoTenNguoiNhan"></span>
                                <span class="field-validation-valid" data-valmsg-for="receiver_name"></span>
                            </div>

                            <!-- Số điện thoại người nhận -->
                            <div class="form-group">
                                <label class="form-label" for="SoDienThoai">Số điện thoại nhận hàng <span class="required-mark">*</span></label>
                                <div class="input-with-icon">
                                    <input type="tel" class="form-control" id="SoDienThoai" name="receiver_phone"
                                        placeholder="Ví dụ: 0909123456 (10 chữ số)"
                                        value="{{ old('receiver_phone', auth()->user()->phone ?? '') }}"
                                        maxlength="11" required autocomplete="tel" pattern="0[0-9]{9,10}">
                                    <i class="fa-solid fa-phone input-icon"></i>
                                </div>
                                <span class="field-validation-valid" data-valmsg-for="SoDienThoai"></span>
                                <span class="field-validation-valid" data-valmsg-for="receiver_phone"></span>
                            </div>

                            <!-- Địa chỉ chi tiết (số nhà, đường) -->
                            <div class="form-group">
                                <label class="form-label" for="DiaChiGiaoHang">Địa chỉ chi tiết (Số nhà, tên đường) <span class="required-mark">*</span></label>
                                <div class="input-with-icon">
                                    <input type="text" class="form-control" id="DiaChiGiaoHang" name="receiver_address"
                                        placeholder="Số nhà, tên đường, khu dân cư..."
                                        value="{{ old('receiver_address', auth()->user()->address ?? '') }}"
                                        maxlength="500" required autocomplete="street-address">
                                    <i class="fa-solid fa-location-dot input-icon"></i>
                                </div>
                                <span class="field-validation-valid" data-valmsg-for="DiaChiGiaoHang"></span>
                                <span class="field-validation-valid" data-valmsg-for="receiver_address"></span>
                            </div>

                            <!-- Khu vực hành chính Việt Nam (Autocomplete & Cascade) -->
                            <div class="address-preset-bar">
                                <span class="address-preset-label"><i class="fa-solid fa-bolt text-warning"></i> Chọn nhanh khu vực:</span>
                                <button type="button" class="preset-chip" onclick="quickSelectCity('TP Hồ Chí Minh')">TP. Hồ Chí Minh</button>
                                <button type="button" class="preset-chip" onclick="quickSelectCity('Hà Nội')">Hà Nội</button>
                                <button type="button" class="preset-chip" onclick="quickSelectCity('Đà Nẵng')">Đà Nẵng</button>
                                <button type="button" class="preset-chip" onclick="quickSelectCity('Cần Thơ')">Cần Thơ</button>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="TinhThanh">Tỉnh / Thành phố <span class="required-mark">*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="TinhThanh" name="tinh_thanh"
                                            placeholder="Gõ tìm Tỉnh / Thành..." autocomplete="off">
                                        <i class="fa-solid fa-map-location-dot input-icon"></i>
                                    </div>
                                    <span class="field-validation-valid" data-valmsg-for="TinhThanh"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="QuanHuyen">Quận / Huyện <span class="required-mark">*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="QuanHuyen" name="quan_huyen"
                                            placeholder="Gõ tìm Quận / Huyện..." autocomplete="off">
                                        <i class="fa-solid fa-building input-icon"></i>
                                    </div>
                                    <span class="field-validation-valid" data-valmsg-for="QuanHuyen"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="PhuongXa">Phường / Xã <span class="required-mark">*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="PhuongXa" name="phuong_xa"
                                            placeholder="Gõ tìm Phường / Xã..." autocomplete="off">
                                        <i class="fa-solid fa-tree-city input-icon"></i>
                                    </div>
                                    <span class="field-validation-valid" data-valmsg-for="PhuongXa"></span>
                                </div>
                            </div>

                            <!-- Ghi chú đơn hoa -->
                            <div class="form-group">
                                <label class="form-label" for="note">Ghi chú đơn hoa / Nội dung thiệp chúc mừng (Tùy chọn)</label>
                                <textarea class="form-control" id="note" name="note" rows="3"
                                    maxlength="1000"
                                    placeholder="Nhập lời chúc muốn in lên thiệp, thời gian hẹn giao (ví dụ: giao trước 10h sáng ngày 20/10), yêu cầu về màu sắc ruy băng...">{{ old('note') }}</textarea>
                            </div>
                        </section>

                        <!-- SECTION 2: Phương thức thanh toán -->
                        <section class="form-section">
                            <div class="form-section-header">
                                <h3><span class="section-num">2</span> Phương thức thanh toán</h3>
                            </div>

                            <div class="payment-methods-list">
                                <!-- Chuyển khoản VietQR & MoMo -->
                                <div class="payment-method-card active" id="card-banking" onclick="selectPaymentMethod('Banking')">
                                    <div class="payment-method-header">
                                        <input type="radio" id="momo" name="payment_method" value="Banking"
                                            @checked(old('payment_method')==='Banking' || old('payment_method')===null)>
                                        <div class="payment-label-wrap">
                                            <span class="payment-title">Chuyển khoản Ngân hàng qua VietQR / MoMo</span>
                                            <span class="payment-desc">Quét mã QR thanh toán nhanh chóng qua bất kỳ App Ngân hàng hoặc Ví MoMo</span>
                                        </div>
                                        <div class="payment-logos">
                                            <img src="{{ asset('images/MoMo.png') }}" alt="MoMo" class="payment-logo" onerror="this.style.display='none'">
                                            <span style="font-size: 11px; font-weight: 700; color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 2px 8px; border-radius: 4px;">VietQR</span>
                                        </div>
                                    </div>

                                    <div class="payment-subpanel">
                                        <div class="banking-info-box">
                                            <p class="banking-qr-code">Mã VietQR theo tổng tiền sau giảm sẽ hiển thị sau khi xác nhận đơn hàng. Chưa chuyển tiền ở bước này.</p>
                                            <div class="banking-details">
                                                <div class="banking-row">
                                                    <span class="banking-label">Ngân hàng:</span>
                                                    <span class="banking-val">MB Bank (Ngân Hàng Quân Đội)</span>
                                                </div>
                                                <div class="banking-row">
                                                    <span class="banking-label">Số tài khoản:</span>
                                                    <span class="banking-val">
                                                        <span>0898449950</span>
                                                        <button type="button" class="btn-copy-acc" onclick="event.stopPropagation(); copyToClipboard('0898449950')" title="Sao chép STK">
                                                            <i class="fa-regular fa-copy"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                                <div class="banking-row">
                                                    <span class="banking-label">Chủ tài khoản:</span>
                                                    <span class="banking-val">GÓC HOA XINH</span>
                                                </div>
                                                <div class="banking-row">
                                                    <span class="banking-label">Cú pháp CK:</span>
                                                    <span class="banking-val">Mã đơn sẽ được tạo khi xác nhận</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thanh toán COD -->
                                <div class="payment-method-card" id="card-cod" onclick="selectPaymentMethod('COD')">
                                    <div class="payment-method-header">
                                        <input type="radio" id="cod" name="payment_method" value="COD"
                                            @checked(old('payment_method')==='COD' )>
                                        <div class="payment-label-wrap">
                                            <span class="payment-title">Thanh toán khi nhận hàng (COD)</span>
                                            <span class="payment-desc">Kiểm tra độ tươi nguyên của hoa và ký nhận trước khi thanh toán tiền mặt cho shipper</span>
                                        </div>
                                        <div class="payment-icon-cod">
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="field-validation-valid" data-valmsg-for="payment_method"></span>
                            <span class="field-validation-valid" data-valmsg-for="PhuongThucThanhToan"></span>
                        </section>

                        <!-- Actions Bar (Quay về giỏ hàng & Đặt hàng) -->
                        <div class="form-actions-bar">
                            <a href="{{ route('cart.index') }}" class="back-to-cart-link">
                                <i class="fa-solid fa-arrow-left-long"></i> Quay về giỏ hàng
                            </a>
                            <button type="submit" class="btn btn-place-order" id="btnPlaceOrder">
                                <span class="btn-spinner" id="btnSpinner"></span>
                                <span class="btn-text" id="btnText">ĐẶT HÀNG NGAY</span>
                                <i class="fa-solid fa-arrow-right-long" id="btnIcon"></i>
                            </button>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Order Summary Sidebar -->
                    <div class="sidebar-content" id="sidebarContent">
                        <h4 class="sidebar-title">
                            <span>Đơn hàng của bạn</span>
                            <span class="item-count-pill">{{ count($lines) }} sản phẩm</span>
                        </h4>

                        <!-- Items List -->
                        <div class="order-summary-list">
                            @foreach($lines as $line)
                            @php
                            $prod = $line['product'];
                            $imgUrl = $prod ? \App\Models\Product::resolveImageUrl($prod->image) : null;
                            if (! $imgUrl) {
                            $imgUrl = asset('images/cate_1.png');
                            }
                            @endphp
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="{{ $imgUrl }}" alt="{{ $prod?->name ?? 'Sản phẩm hoa tươi' }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/cate_1.png') }}';">
                                    <span class="item-quantity">{{ $line['quantity'] }}</span>
                                </div>
                                <div class="item-info">
                                    <div class="item-name">{{ $prod?->name ?? 'Sản phẩm không còn tồn tại' }}</div>
                                    <div class="item-code-badge">{{ $line['code'] }}</div>
                                </div>
                                <div class="item-price">
                                    {{ number_format($line['subtotal'], 0, ',', '.') }} đ
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Voucher Code Box -->
                        <div class="discount-section">
                            <div class="discount-input-wrap">
                                <input type="text" class="form-control" id="voucherInput" value="{{ $voucher_code }}" maxlength="50" aria-label="Mã ưu đãi" placeholder="Nhập mã ưu đãi..." autocomplete="off">
                            </div>
                            <button type="button" class="btn-apply-voucher" id="btnApplyVoucher" data-url="{{ route('checkout.voucher') }}">Áp dụng</button>
                        </div>
                        <div class="voucher-tags-row">
                            <span class="voucher-tag" onclick="applyVoucherPreset('HOATUOI2026')">🌸 HOATUOI2026 (-50k)</span>
                            <span class="voucher-tag" onclick="applyVoucherPreset('FREESHIP')">🚚 FREESHIP (Miễn phí ship)</span>
                        </div>
                        <div id="voucherFeedback" role="status" aria-live="polite" style="font-size: 12.5px; margin-bottom: 14px; font-weight: 600;">{{ $voucher_code ? 'Đang áp dụng mã '.$voucher_code : '' }}</div>

                        <!-- Fee Breakdown -->
                        <div class="total-section">
                            <div class="total-row">
                                <span>Tạm tính</span>
                                <span id="subtotalDisplay">{{ number_format($subtotal_amount, 0, ',', '.') }} đ</span>
                            </div>
                            <div class="total-row">
                                <span>Phí vận chuyển</span>
                                <span class="val-free">Miễn phí giao hoa 2h</span>
                            </div>
                            <div class="total-row" id="discountRow" @if(!$voucher_code) style="display: none;" @endif>
                                <span>Giảm giá Voucher</span>
                                <span class="text-danger fw-bold" id="discountDisplay">- {{ number_format($discount_amount, 0, ',', '.') }} đ</span>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="total-row grand-total">
                            <span class="total-label">Tổng cộng</span>
                            <span class="total-price" id="grandTotalPrice">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>

                        <!-- Trust Guarantees (Floral Boutique Promises) -->
                        <div class="floral-guarantees">
                            <div class="guarantee-item">
                                <div class="guarantee-icon"><i class="fa-solid fa-seedling"></i></div>
                                <div><strong>100% Hoa tươi nhập mới</strong> mỗi sáng từ các nông trại Đà Lạt.</div>
                            </div>
                            <div class="guarantee-item">
                                <div class="guarantee-icon"><i class="fa-solid fa-clock"></i></div>
                                <div><strong>Giao nhanh hỏa tốc 2 giờ</strong> đảm bảo hoa giữ nguyên độ tươi mới.</div>
                            </div>
                            <div class="guarantee-item">
                                <div class="guarantee-icon"><i class="fa-solid fa-camera"></i></div>
                                <div><strong>Chụp ảnh hoa thực tế</strong> gửi khách duyệt trước khi xuất xưởng.</div>
                            </div>
                            <div class="guarantee-item">
                                <div class="guarantee-icon"><i class="fa-solid fa-gift"></i></div>
                                <div><strong>Tặng miễn phí thiệp & ruy băng</strong> thiết kế sang trọng theo thông điệp.</div>
                            </div>
                        </div>

                    </div>

                </div>
            </form>
        </main>

        <!-- Minimalist Footer -->
        <footer style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px; border-top: 1px solid var(--border-color); background: #ffffff;">
            <p class="mb-1">&copy; {{ date('Y') }} <strong>Góc Hoa Xinh (Blossom Corner)</strong> - Nghệ thuật hoa tươi trao gửi yêu thương.</p>
            <p class="mb-0" style="font-size: 12px; color: var(--text-light);">Địa chỉ: 140 Lê Trọng Tấn, P. Tây Thạnh, Q. Tân Phú, TP. Hồ Chí Minh &bull; Hotline: 0898.449.950</p>
        </footer>

    </div>

    <!-- ==========================================================================
         JAVASCRIPT LOGIC (VALIDATION, AUTOCOMPLETE, CASCADE & VOUCHERS)
         Ported & Enhanced from C# ASP MVC 5 ThanhToan.cshtml & Playwright test-cases
         ========================================================================== -->
    <script>
        // Data Tỉnh / Thành, Quận / Huyện, Phường / Xã chuẩn Việt Nam (Đầy đủ 63 tỉnh thành & quận huyện lớn)
        var tinhThanhData = [
            "TP Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Hải Phòng", "Cần Thơ",
            "Bình Dương", "Đồng Nai", "Bà Rịa - Vũng Tàu", "Lâm Đồng", "Khánh Hòa",
            "An Giang", "Bắc Giang", "Bắc Kạn", "Bạc Liêu", "Bắc Ninh",
            "Bến Tre", "Bình Định", "Bình Phước", "Bình Thuận", "Cà Mau",
            "Cao Bằng", "Đắk Lắk", "Đắk Nông", "Điện Biên", "Đồng Tháp",
            "Gia Lai", "Hà Giang", "Hà Nam", "Hà Tĩnh", "Hải Dương",
            "Hậu Giang", "Hòa Bình", "Hưng Yên", "Kiên Giang", "Kon Tum",
            "Lai Châu", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định",
            "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên",
            "Quảng Bình", "Quảng Nam", "Quảng Ngãi", "Quảng Ninh", "Quảng Trị",
            "Sóc Trăng", "Sơn La", "Tây Ninh", "Thái Bình", "Thái Nguyên",
            "Thanh Hóa", "Thừa Thiên Huế", "Tiền Giang", "Trà Vinh", "Tuyên Quang",
            "Vĩnh Long", "Vĩnh Phúc", "Yên Bái"
        ];

        var quanHuyenData = {
            "TP Hồ Chí Minh": [
                "Quận 1", "Quận 3", "Quận 4", "Quận 5", "Quận 6",
                "Quận 7", "Quận 8", "Quận 10", "Quận 11", "Quận 12",
                "Bình Thạnh", "Tân Bình", "Tân Phú", "Phú Nhuận", "Gò Vấp",
                "Bình Tân", "TP Thủ Đức", "Hóc Môn", "Bình Chánh", "Nhà Bè", "Củ Chi", "Cần Giờ"
            ],
            "Hà Nội": [
                "Ba Đình", "Hoàn Kiếm", "Tây Hồ", "Long Biên", "Cầu Giấy",
                "Đống Đa", "Hai Bà Trưng", "Hoàng Mai", "Thanh Xuân", "Hà Đông",
                "Nam Từ Liêm", "Bắc Từ Liêm", "Sơn Tây", "Ba Vì", "Đông Anh",
                "Gia Lâm", "Hoài Đức", "Mê Linh", "Sóc Sơn", "Thạch Thất", "Thanh Trì", "Thường Tín"
            ],
            "Đà Nẵng": [
                "Hải Châu", "Thanh Khê", "Sơn Trà", "Ngũ Hành Sơn", "Liên Chiểu", "Cẩm Lệ", "Hòa Vang"
            ],
            "Cần Thơ": [
                "Ninh Kiều", "Bình Thủy", "Cái Răng", "Ô Môn", "Thốt Nốt", "Phong Điền", "Cờ Đỏ", "Thới Lai", "Vĩnh Thạnh"
            ],
            "Hải Phòng": [
                "Hồng Bàng", "Ngô Quyền", "Lê Chân", "Hải An", "Kiến An", "Đồ Sơn", "Dương Kinh", "Thủy Nguyên", "An Dương"
            ],
            "Bình Dương": [
                "Thủ Dầu Một", "Thuận An", "Dĩ An", "Bến Cát", "Tân Uyên", "Bàu Bàng", "Bắc Tân Uyên"
            ],
            "Đồng Nai": [
                "Biên Hòa", "Long Khánh", "Long Thành", "Nhơn Trạch", "Trảng Bom", "Thống Nhất"
            ],
            "Bà Rịa - Vũng Tàu": [
                "Vũng Tàu", "Bà Rịa", "Phú Mỹ", "Châu Đức", "Long Điền", "Đất Đỏ", "Xuyên Mộc"
            ],
            "Lâm Đồng": [
                "Đà Lạt", "Bảo Lộc", "Đức Trọng", "Di Linh", "Đơn Dương", "Lạc Dương"
            ],
            "Khánh Hòa": [
                "Nha Trang", "Cam Ranh", "Ninh Hòa", "Vạn Ninh", "Diên Khánh"
            ],
            "Thừa Thiên Huế": [
                "TP Huế", "Hương Thủy", "Hương Trà", "Phú Vang", "Quảng Điền"
            ],
            "Huế": [
                "TP Huế", "Hương Thủy", "Hương Trà", "Phú Vang", "Quảng Điền"
            ]
        };

        var phuongXaData = {
            "Quận 1": [
                "Bến Nghé", "Bến Thành", "Cô Giang", "Cầu Kho", "Cầu Ông Lãnh",
                "Đa Kao", "Nguyễn Cư Trinh", "Nguyễn Thái Bình", "Phạm Ngũ Lão", "Tân Định"
            ],
            "Quận 3": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
                "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Võ Thị Sáu"
            ],
            "Quận 4": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 6", "Phường 8", "Phường 9", "Phường 10", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 18"
            ],
            "Quận 5": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"
            ],
            "Quận 7": [
                "Bình Thuận", "Phú Mỹ", "Phú Thuận", "Tân Hưng", "Tân Kiểng",
                "Tân Phong", "Tân Phú", "Tân Quy", "Tân Thuận Đông", "Tân Thuận Tây"
            ],
            "Quận 10": [
                "Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
            ],
            "Bình Thạnh": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6", "Phường 7",
                "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17",
                "Phường 19", "Phường 21", "Phường 22", "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"
            ],
            "Tân Bình": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6",
                "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
            ],
            "Tân Phú": [
                "Hiệp Tân", "Hòa Thạnh", "Phú Thạnh", "Phú Thọ Hòa", "Phú Trung",
                "Sơn Kỳ", "Tân Quý", "Tân Sơn Nhì", "Tân Thành", "Tân Thới Hòa", "Tây Thạnh"
            ],
            "Phú Nhuận": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7",
                "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 13", "Phường 15", "Phường 17"
            ],
            "Gò Vấp": [
                "Phường 1", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7",
                "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 14", "Phường 15", "Phường 16", "Phường 17"
            ],
            "Bình Tân": [
                "An Lạc", "An Lạc A", "Bình Hưng Hòa", "Bình Hưng Hòa A", "Bình Hưng Hòa B",
                "Bình Trị Đông", "Bình Trị Đông A", "Bình Trị Đông B", "Tân Tạo", "Tân Tạo A"
            ],
            "TP Thủ Đức": [
                "An Khánh", "An Lợi Đông", "An Phú", "Bình Chiểu", "Bình Thọ",
                "Hiệp Bình Chánh", "Hiệp Bình Phước", "Hiệp Phú", "Linh Chiểu", "Linh Đông",
                "Linh Trung", "Linh Xuân", "Phước Bình", "Phước Long A", "Phước Long B",
                "Tam Bình", "Tam Phú", "Tăng Nhơn Phú A", "Tăng Nhơn Phú B", "Thảo Điền", "Thủ Thiêm"
            ],
            "Ba Đình": [
                "Cống Vị", "Điện Biên", "Đội Cấn", "Giảng Võ", "Kim Mã",
                "Liễu Giai", "Ngọc Hà", "Ngọc Khánh", "Nguyễn Trung Trực", "Phúc Xá",
                "Quán Thánh", "Thành Công", "Trúc Bạch", "Vĩnh Phúc"
            ],
            "Hoàn Kiếm": [
                "Chương Dương", "Cửa Đông", "Cửa Nam", "Đồng Xuân", "Hàng Bạc",
                "Hàng Bài", "Hàng Bồ", "Hàng Bông", "Hàng Buồm", "Hàng Đào",
                "Hàng Gai", "Hàng Mã", "Hàng Trống", "Lý Thái Tổ", "Phan Chu Trinh", "Trần Hưng Đạo", "Tràng Tiền"
            ],
            "Đống Đa": [
                "Cát Linh", "Hàng Bột", "Khâm Thiên", "Khương Thượng", "Kim Liên",
                "Láng Hạ", "Láng Thượng", "Nam Đồng", "Ngã Tư Sở", "Ô Chợ Dừa",
                "Phương Liên", "Phương Mai", "Quang Trung", "Quốc Tử Giám", "Thịnh Quang", "Văn Miếu"
            ],
            "Cầu Giấy": [
                "Dịch Vọng", "Dịch Vọng Hậu", "Mai Dịch", "Nghĩa Đô", "Nghĩa Tân", "Quan Hoa", "Trung Hòa", "Yên Hòa"
            ],
            "Hai Bà Trưng": [
                "Bạch Đằng", "Bách Khoa", "Bạch Mai", "Cầu Dền", "Đống Mác", "Đồng Nhân", "Đồng Tâm", "Lê Đại Hành", "Minh Khai", "Nguyễn Du", "Trương Định", "Vĩnh Tuy"
            ],
            "Thanh Xuân": [
                "Hạ Đình", "Khương Đình", "Khương Mai", "Khương Trung", "Kim Giang", "Nhân Chính", "Phương Liệt", "Thanh Xuân Bắc", "Thanh Xuân Nam", "Thanh Xuân Trung", "Thượng Đình"
            ],
            "Hải Châu": [
                "Bình Hiên", "Bình Thuận", "Hải Châu 1", "Hải Châu 2", "Hòa Cường Bắc", "Hòa Cường Nam",
                "Hòa Thuận Đông", "Hòa Thuận Tây", "Nam Dương", "Phước Ninh", "Thạch Thang", "Thanh Bình", "Thuận Phước"
            ],
            "Ninh Kiều": [
                "An Bình", "An Cư", "An Hòa", "An Khánh", "An Nghiệp", "Cái Khế", "Hưng Lợi", "Tân An", "Thới Bình", "Xuân Khánh"
            ],
            "Thủ Dầu Một": [
                "Chánh Mỹ", "Chánh Nghĩa", "Định Hòa", "Hiệp An", "Hiệp Thành", "Hòa Phú", "Phú Cường", "Phú Hòa", "Phú Lợi", "Phú Mỹ"
            ],
            "Biên Hòa": [
                "An Bình", "An Hòa", "Bình Đa", "Bửu Hòa", "Bửu Long", "Hiệp Hòa", "Hóa An", "Hòa Bình", "Hố Nai", "Long Bình", "Tân Phong"
            ],
            "Đà Lạt": [
                "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6",
                "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12"
            ]
        };

        // Chuẩn hóa chuỗi tiếng Việt không dấu để tìm kiếm thông minh
        function removeVietnameseTones(str) {
            if (!str) return '';
            return str
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'd')
                .trim();
        }

        function closeAllLists(elmnt) {
            var items = document.getElementsByClassName("autocomplete-items");
            for (var i = items.length - 1; i >= 0; i--) {
                var container = items[i];
                if (elmnt !== container && (!container.previousElementSibling || elmnt !== container.previousElementSibling)) {
                    if (container.parentNode) {
                        container.parentNode.style.zIndex = "";
                        container.parentNode.removeChild(container);
                    }
                }
            }
        }

        // Hàm Autocomplete thông minh có hỗ trợ bàn phím (Up, Down, Enter, Esc), gõ không dấu, hiển thị từ 1 ký tự
        function setupAutocomplete(inp, dataProvider) {
            if (!inp) return;

            // Cập nhật nguồn dữ liệu cho input (có thể là mảng hoặc hàm trả về mảng)
            inp._getAutocompleteData = typeof dataProvider === 'function' ? dataProvider : function() {
                return Array.isArray(dataProvider) ? dataProvider : [];
            };

            // Nếu đã gán các sự kiện trước đó thì không gán lại tránh trùng lặp listener
            if (inp._autocompleteInitialized) return;
            inp._autocompleteInitialized = true;

            var currentFocus = -1;

            function renderDropdown(val) {
                closeAllLists();
                var data = inp._getAutocompleteData() || [];
                if (!data || data.length === 0) return;

                var cleanVal = (val || "").trim();
                var normalizedVal = removeVietnameseTones(cleanVal);
                var matches = [];

                if (!cleanVal) {
                    // Khi chưa gõ ký tự nào, hiển thị danh sách gợi ý mẫu (tối đa 12 kết quả)
                    matches = data.slice(0, 12);
                } else {
                    matches = data.filter(function(item) {
                        var rawItem = item.toLowerCase();
                        var rawQuery = cleanVal.toLowerCase();

                        // 1. So khớp trực tiếp chuỗi con (có dấu)
                        if (rawItem.indexOf(rawQuery) > -1) return true;

                        // 2. So khớp không dấu tiếng Việt (vd: "da nang" -> "Đà Nẵng", "d" -> "Đà Nẵng")
                        var normItem = removeVietnameseTones(item);
                        if (normItem.indexOf(normalizedVal) > -1) return true;

                        // 3. Viết tắt phổ biến
                        if (rawQuery === 'hcm' || rawQuery === 'sg' || rawQuery === 'tphcm') {
                            return normItem.indexOf('ho chi minh') > -1;
                        }
                        if (rawQuery === 'hn') {
                            return normItem.indexOf('ha noi') > -1;
                        }
                        if (rawQuery === 'dn') {
                            return normItem.indexOf('da nang') > -1;
                        }
                        return false;
                    }).slice(0, 15);
                }

                if (matches.length === 0) return;

                currentFocus = -1;
                if (inp.parentNode) {
                    inp.parentNode.style.zIndex = "35";
                }

                var container = document.createElement("DIV");
                container.setAttribute("class", "autocomplete-items");
                inp.parentNode.appendChild(container);

                matches.forEach(function(itemText) {
                    var itemDiv = document.createElement("DIV");

                    var matchPos = -1;
                    var matchLen = cleanVal.length;

                    if (cleanVal) {
                        matchPos = itemText.toLowerCase().indexOf(cleanVal.toLowerCase());
                        if (matchPos === -1) {
                            var normItem = removeVietnameseTones(itemText);
                            matchPos = normItem.indexOf(normalizedVal);
                        }
                    }

                    if (matchPos > -1 && cleanVal) {
                        itemDiv.innerHTML = itemText.substring(0, matchPos) +
                            "<strong>" + itemText.substring(matchPos, matchPos + matchLen) + "</strong>" +
                            itemText.substring(matchPos + matchLen);
                    } else {
                        itemDiv.textContent = itemText;
                    }

                    itemDiv.setAttribute("data-value", itemText);

                    itemDiv.addEventListener("click", function(e) {
                        e.stopPropagation();
                        var selectedVal = this.getAttribute("data-value") || this.textContent;
                        inp.value = selectedVal;
                        closeAllLists();
                        var changeEvt = new Event('change', { bubbles: true });
                        inp.dispatchEvent(changeEvt);
                        syncFullAddress();
                    });

                    container.appendChild(itemDiv);
                });
            }

            // Gợi ý ngay lập tức khi gõ từ 1 ký tự
            inp.addEventListener("input", function(e) {
                renderDropdown(this.value);
            });

            // Gợi ý khi click hoặc focus vào ô
            inp.addEventListener("focus", function(e) {
                renderDropdown(this.value);
            });

            inp.addEventListener("click", function(e) {
                e.stopPropagation();
                renderDropdown(this.value);
            });

            // Hỗ trợ bàn phím (Phím Lên, Xuống, Enter, Escape)
            inp.addEventListener("keydown", function(e) {
                var container = this.parentNode ? this.parentNode.querySelector(".autocomplete-items") : null;
                if (!container) return;
                var items = container.getElementsByTagName("div");
                if (!items || items.length === 0) return;

                if (e.keyCode === 40) { // Down
                    e.preventDefault();
                    currentFocus++;
                    addActive(items);
                } else if (e.keyCode === 38) { // Up
                    e.preventDefault();
                    currentFocus--;
                    addActive(items);
                } else if (e.keyCode === 13) { // Enter
                    if (currentFocus > -1 && items[currentFocus]) {
                        e.preventDefault();
                        items[currentFocus].click();
                    }
                } else if (e.keyCode === 27) { // Escape
                    closeAllLists();
                }
            });

            function addActive(items) {
                if (!items || items.length === 0) return false;
                removeActive(items);
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = items.length - 1;
                items[currentFocus].classList.add("autocomplete-active");
                items[currentFocus].scrollIntoView({
                    block: "nearest"
                });
            }

            function removeActive(items) {
                for (var i = 0; i < items.length; i++) {
                    items[i].classList.remove("autocomplete-active");
                }
            }
        }

        // Đóng dropdown khi click ra ngoài
        document.addEventListener("click", function(e) {
            closeAllLists(e.target);
        });

        // Chọn nhanh Tỉnh thành qua Preset chips
        function quickSelectCity(cityName) {
            var tinhInput = document.getElementById("TinhThanh");
            if (tinhInput) {
                tinhInput.value = cityName;
                tinhInput.dispatchEvent(new Event('input', { bubbles: true }));
                tinhInput.dispatchEvent(new Event('change', { bubbles: true }));
                syncFullAddress();
            }
        }

        // Tự động đồng bộ địa chỉ đầy đủ vào receiver_address (DiaChiGiaoHang)
        function syncFullAddress() {
            var addressInput = document.getElementById("DiaChiGiaoHang");
            var tinhInput = document.getElementById("TinhThanh");
            var quanInput = document.getElementById("QuanHuyen");
            var phuongInput = document.getElementById("PhuongXa");

            if (!addressInput) return;

            var baseAddress = addressInput.value.trim();
            var tinh = tinhInput ? tinhInput.value.trim() : "";
            var quan = quanInput ? quanInput.value.trim() : "";
            var phuong = phuongInput ? phuongInput.value.trim() : "";

            // Nếu ô địa chỉ đã có số nhà nhưng chưa có phường/quận/tỉnh được chọn, hỗ trợ ghép logic
            // Không ghi đè nếu người dùng đã gõ trọn vẹn địa chỉ có dấu phẩy
        }

        // Chuyển đổi phương thức thanh toán
        function selectPaymentMethod(method) {
            var momoRadio = document.getElementById("momo");
            var codRadio = document.getElementById("cod");
            var cardBanking = document.getElementById("card-banking");
            var cardCod = document.getElementById("card-cod");

            if (method === 'Banking') {
                if (momoRadio) momoRadio.checked = true;
                if (cardBanking) cardBanking.classList.add("active");
                if (cardCod) cardCod.classList.remove("active");
            } else if (method === 'COD') {
                if (codRadio) codRadio.checked = true;
                if (cardCod) cardCod.classList.add("active");
                if (cardBanking) cardBanking.classList.remove("active");
            }

            // Xóa lỗi validation payment nếu có
            var errSpans = document.querySelectorAll("[data-valmsg-for='payment_method'], [data-valmsg-for='PhuongThucThanhToan']");
            errSpans.forEach(function(span) {
                span.textContent = "";
                span.className = "field-validation-valid";
            });
        }

        // Sao chép nhanh số tài khoản
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    alert("Đã sao chép số tài khoản MB Bank: " + text);
                });
            } else {
                alert("Số tài khoản MB Bank: " + text);
            }
        }

        // Khởi tạo khi DOM sẵn sàng
        document.addEventListener('DOMContentLoaded', function() {

            // 1. Khởi tạo Autocomplete Địa chỉ
            var tinhThanhInput = document.getElementById("TinhThanh");
            var quanHuyenInput = document.getElementById("QuanHuyen");
            var phuongXaInput = document.getElementById("PhuongXa");

            if (tinhThanhInput) {
                setupAutocomplete(tinhThanhInput, tinhThanhData);

                tinhThanhInput.addEventListener("change", function() {
                    var selectedTinh = this.value ? this.value.trim() : "";
                    if (quanHuyenInput) {
                        quanHuyenInput.value = "";
                        var districts = quanHuyenData[selectedTinh] || [];
                        setupAutocomplete(quanHuyenInput, districts);
                    }
                    if (phuongXaInput) {
                        phuongXaInput.value = "";
                        setupAutocomplete(phuongXaInput, []);
                    }
                });
            }

            if (quanHuyenInput) {
                var initialDistricts = (tinhThanhInput && tinhThanhInput.value && quanHuyenData[tinhThanhInput.value.trim()]) ?
                    quanHuyenData[tinhThanhInput.value.trim()] : (quanHuyenData["TP Hồ Chí Minh"] || []);
                setupAutocomplete(quanHuyenInput, initialDistricts);

                quanHuyenInput.addEventListener("change", function() {
                    var selectedQuan = this.value ? this.value.trim() : "";
                    if (phuongXaInput) {
                        phuongXaInput.value = "";
                        var wards = phuongXaData[selectedQuan] || [];
                        setupAutocomplete(phuongXaInput, wards);
                    }
                });
            }

            if (phuongXaInput) {
                var initialWards = (quanHuyenInput && quanHuyenInput.value && phuongXaData[quanHuyenInput.value.trim()]) ?
                    phuongXaData[quanHuyenInput.value.trim()] : (phuongXaData["Quận 1"] || []);
                setupAutocomplete(phuongXaInput, initialWards);
            }

            // 2. Mobile Drawer Toggle
            var mobileToggleBtn = document.getElementById("mobileToggleBtn");
            var mobileToggleIcon = document.getElementById("mobileToggleIcon");
            var sidebarContent = document.getElementById("sidebarContent");

            if (mobileToggleBtn && sidebarContent) {
                mobileToggleBtn.addEventListener("click", function() {
                    sidebarContent.classList.toggle("mobile-visible");
                    mobileToggleBtn.classList.toggle("open");
                });
            }

            // ==================================================================
            // REAL-TIME VALIDATION ENGINE (Ported from C# Shop-Flower & PAY_01-PAY_04)
            // ==================================================================
            var form = document.getElementById("checkoutForm");
            var emailInput = document.getElementById("Email");
            var nameInput = document.getElementById("HoTenNguoiNhan");
            var phoneInput = document.getElementById("SoDienThoai");
            var addressInput = document.getElementById("DiaChiGiaoHang");
            var momoRadio = document.getElementById("momo");
            var codRadio = document.getElementById("cod");

            // [PAY_01]: Xác thực trường Số điện thoại tự động lọc chữ cái (digits only)
            if (phoneInput) {
                phoneInput.addEventListener("input", function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }

            // Hàm hiển thị lỗi chuẩn
            function showError(inputElement, fieldNames, message) {
                if (inputElement) {
                    inputElement.classList.add("is-invalid");
                    inputElement.classList.remove("is-valid");
                }
                var names = Array.isArray(fieldNames) ? fieldNames : [fieldNames];
                names.forEach(function(name) {
                    var spans = document.querySelectorAll("[data-valmsg-for='" + name + "']");
                    spans.forEach(function(span) {
                        span.textContent = message;
                        span.className = "field-validation-error";
                    });
                });
                return false;
            }

            // Hàm xóa lỗi khi hợp lệ
            function clearError(inputElement, fieldNames) {
                if (inputElement) {
                    inputElement.classList.remove("is-invalid");
                    inputElement.classList.add("is-valid");
                }
                var names = Array.isArray(fieldNames) ? fieldNames : [fieldNames];
                names.forEach(function(name) {
                    var spans = document.querySelectorAll("[data-valmsg-for='" + name + "']");
                    spans.forEach(function(span) {
                        span.textContent = "";
                        span.className = "field-validation-valid";
                    });
                });
                return true;
            }

            // [PAY_02]: Kiểm tra Email
            function validateEmail() {
                if (!emailInput) return true;
                var val = emailInput.value.trim();
                if (!val) {
                    return showError(emailInput, ["Email", "email"], "Vui lòng nhập email của bạn");
                }
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(val)) {
                    return showError(emailInput, ["Email", "email"], "Email không hợp lệ (ví dụ: blossom@example.com)");
                }
                return clearError(emailInput, ["Email", "email"]);
            }

            // Kiểm tra Họ và Tên người nhận
            function validateName() {
                if (!nameInput) return true;
                var val = nameInput.value.trim();
                if (!val) {
                    return showError(nameInput, ["HoTenNguoiNhan", "receiver_name"], "Vui lòng nhập họ và tên người nhận hoa");
                }
                if (val.length < 2) {
                    return showError(nameInput, ["HoTenNguoiNhan", "receiver_name"], "Họ tên người nhận phải có từ 2 ký tự trở lên");
                }
                return clearError(nameInput, ["HoTenNguoiNhan", "receiver_name"]);
            }

            // Kiểm tra Số điện thoại
            function validatePhone() {
                if (!phoneInput) return true;
                var val = phoneInput.value.trim();
                if (!val) {
                    return showError(phoneInput, ["SoDienThoai", "receiver_phone"], "Vui lòng nhập số điện thoại nhận hàng");
                }
                var phoneRegex = /^0[0-9]{9,10}$/;
                if (!phoneRegex.test(val)) {
                    return showError(phoneInput, ["SoDienThoai", "receiver_phone"], "Số điện thoại không hợp lệ (gồm 10-11 chữ số, bắt đầu bằng 0)");
                }
                return clearError(phoneInput, ["SoDienThoai", "receiver_phone"]);
            }

            // Kiểm tra Địa chỉ giao hàng
            function validateAddress() {
                if (!addressInput) return true;
                var val = addressInput.value.trim();
                if (!val) {
                    return showError(addressInput, ["DiaChiGiaoHang", "receiver_address"], "Vui lòng nhập địa chỉ giao hàng chi tiết");
                }
                if (val.length < 5) {
                    return showError(addressInput, ["DiaChiGiaoHang", "receiver_address"], "Địa chỉ quá ngắn, vui lòng nhập rõ số nhà và tên đường");
                }
                return clearError(addressInput, ["DiaChiGiaoHang", "receiver_address"]);
            }

            // Kiểm tra Phương thức thanh toán
            function validatePayment() {
                if (!momoRadio || !codRadio) return true;
                if (!momoRadio.checked && !codRadio.checked) {
                    return showError(null, ["payment_method", "PhuongThucThanhToan"], "Vui lòng chọn phương thức thanh toán");
                }
                return clearError(null, ["payment_method", "PhuongThucThanhToan"]);
            }

            // Gắn lắng nghe sự kiện Real-time Feedback
            if (emailInput) {
                emailInput.addEventListener("blur", validateEmail);
                emailInput.addEventListener("input", validateEmail);
            }
            if (nameInput) {
                nameInput.addEventListener("blur", validateName);
                nameInput.addEventListener("input", validateName);
            }
            if (phoneInput) {
                phoneInput.addEventListener("blur", validatePhone);
                phoneInput.addEventListener("input", validatePhone);
            }
            if (addressInput) {
                addressInput.addEventListener("blur", validateAddress);
                addressInput.addEventListener("input", validateAddress);
            }
            if (momoRadio) momoRadio.addEventListener("change", validatePayment);
            if (codRadio) codRadio.addEventListener("change", validatePayment);

            // [PAY_03]: Gửi form trống -> Tự động cuộn mượt và Focus vào ô lỗi đầu tiên
            if (form) {
                form.addEventListener("submit", function(e) {
                    var isEmailValid = validateEmail();
                    var isNameValid = validateName();
                    var isPhoneValid = validatePhone();
                    var isAddressValid = validateAddress();
                    var isPaymentValid = validatePayment();

                    var isFormValid = isEmailValid && isNameValid && isPhoneValid && isAddressValid && isPaymentValid;

                    if (!isFormValid) {
                        e.preventDefault();

                        // Cuộn mượt và Focus vào phần tử lỗi đầu tiên
                        var firstInvalid = document.querySelector(".form-control.is-invalid");
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            setTimeout(function() {
                                firstInvalid.focus();
                            }, 500);
                        } else {
                            var firstErr = document.querySelector(".field-validation-error");
                            if (firstErr) {
                                firstErr.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        }
                    } else {
                        // Tự động bổ sung Phường/Quận/Tỉnh vào địa chỉ chi tiết nếu người dùng chưa điền đầy đủ
                        if (addressInput) {
                            var rawAddress = addressInput.value.trim();
                            var extraParts = [];
                            if (phuongXaInput && phuongXaInput.value.trim() && rawAddress.toLowerCase().indexOf(phuongXaInput.value.trim().toLowerCase()) === -1) {
                                extraParts.push(phuongXaInput.value.trim());
                            }
                            if (quanHuyenInput && quanHuyenInput.value.trim() && rawAddress.toLowerCase().indexOf(quanHuyenInput.value.trim().toLowerCase()) === -1) {
                                extraParts.push(quanHuyenInput.value.trim());
                            }
                            if (tinhThanhInput && tinhThanhInput.value.trim() && rawAddress.toLowerCase().indexOf(tinhThanhInput.value.trim().toLowerCase()) === -1) {
                                extraParts.push(tinhThanhInput.value.trim());
                            }
                            if (extraParts.length > 0 && rawAddress) {
                                addressInput.value = rawAddress + ", " + extraParts.join(", ");
                            }
                        }

                        // Hiển thị trạng thái Loading trên nút Đặt hàng
                        var btnSubmit = document.getElementById("btnPlaceOrder");
                        var btnSpinner = document.getElementById("btnSpinner");
                        var btnText = document.getElementById("btnText");
                        var btnIcon = document.getElementById("btnIcon");

                        if (btnSubmit) {
                            btnSubmit.disabled = true;
                            if (btnSpinner) btnSpinner.style.display = "inline-block";
                            if (btnText) btnText.textContent = "Đang tạo đơn hoa...";
                            if (btnIcon) btnIcon.style.display = "none";
                            // Gửi form thực tế
                            form.submit();
                        }
                    }
                });
            }

        });
    </script>
</body>

</html>