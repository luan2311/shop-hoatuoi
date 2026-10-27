<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Tài khoản' }} - Góc Hoa Xinh - Shop Hoa Tươi</title>
    <link rel="icon" href="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/favicon.png?1757405855081" type="image/x-icon">

    <!-- Google Fonts: Nunito (gochoaxinh.com standard font) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300;0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #537453;
            --brand-primary-dark: #3e583e;
            --brand-primary-light: #6a916a;
            --brand-accent: #ea8725;
            --brand-pink: #e83e8c;
            --bg-gray: #f8f8f8;
            --text-dark: #212B25;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-gray);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Announcement Bar */
        .top-bar {
            background-color: var(--brand-primary-dark);
            color: #fff;
            font-size: 0.82rem;
            padding: 6px 0;
        }

        .top-bar a {
            color: #fff;
            text-decoration: none;
        }

        .top-bar a:hover {
            color: #ffd285;
        }

        /* Header Main */
        .main-header {
            background: #fff;
            padding: 15px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .header-logo img {
            max-height: 48px;
            width: auto;
            object-fit: contain;
        }

        .header-hotline {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: inherit;
        }

        .header-hotline .icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: var(--brand-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .header-hotline .phone-title {
            font-size: 0.75rem;
            color: #666;
            line-height: 1;
        }

        .header-hotline .phone-number {
            font-size: 1rem;
            font-weight: 800;
            color: var(--brand-primary);
        }

        /* Nav Menu */
        .header-menu {
            background-color: var(--brand-primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .header-menu .navbar-nav .nav-link {
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
            padding: 12px 16px;
            transition: all 0.2s;
        }

        .header-menu .navbar-nav .nav-link:hover {
            background-color: var(--brand-primary-dark);
            color: #ffd285;
        }

        /* Body & Account Styling (Ported from account_style.css) */
        .bodywrap {
            flex-grow: 1;
        }

        .bg-gray {
            background: #f8f8f8;
        }

        .pagecustome .wpx {
            width: 100%;
            position: relative;
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #ebebeb;
        }

        @media (min-width: 768px) {
            .pagecustome .wpx {
                padding: 40px 35px;
            }
        }

        .title_heads {
            font-size: 1.6rem !important;
            font-weight: 800;
            text-transform: uppercase;
            position: relative;
            display: block;
            margin-bottom: 25px;
            text-align: center;
        }

        .title_heads span {
            color: var(--brand-primary);
            position: relative;
            padding-bottom: 10px;
        }

        .title_heads span::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--brand-accent);
            border-radius: 2px;
        }

        /* Form Inputs */
        .form-control-lg {
            font-size: 0.95rem;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #d9d9d9;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.25rem rgba(83, 116, 83, 0.2);
        }

        /* Button Style (Góc Hoa Xinh standard) */
        .btn-style {
            display: inline-block;
            text-align: center;
            height: 46px;
            line-height: 46px;
            background: var(--brand-primary);
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 700;
            padding: 0 30px;
            border-radius: 8px;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(83, 116, 83, 0.3);
        }

        .btn-style:hover {
            background: var(--brand-accent);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(234, 135, 37, 0.35);
        }

        .btn_boz_khac {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.92rem;
        }

        .btn_boz_khac a, .quenmk {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 700;
        }

        .btn_boz_khac a:hover, .quenmk:hover {
            color: var(--brand-accent);
            text-decoration: underline;
        }

        /* SlideUp Animation (Ported from account_style.css) */
        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(35px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .account-animate {
            animation: slideUpFade 0.6s ease-out forwards;
        }

        /* Footer (Ported from _Footer.cshtml) */
        .footer {
            background-color: #2b3a2f;
            color: #d2ddd4;
            padding-top: 50px;
            margin-top: auto;
            border-top: 3px solid var(--brand-primary);
        }

        .footer .footer-logo img {
            max-height: 38px;
            filter: brightness(0) invert(1);
        }

        .footer h5 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 8px;
        }

        .footer h5::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 35px;
            height: 2px;
            background: var(--brand-accent);
        }

        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: #d2ddd4;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 0.92rem;
        }

        .footer ul li a:hover {
            color: var(--brand-accent);
            padding-left: 4px;
        }

        .footer-bottom {
            background-color: #202b23;
            padding: 18px 0;
            margin-top: 40px;
            font-size: 0.85rem;
            color: #9cb1a0;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar d-none d-md-block">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-map-marker-alt me-1 text-warning"></i> 140 Lê Trọng Tấn, P. Tây Thạnh, TP. HCM
                    <span class="mx-2">|</span>
                    <i class="fas fa-envelope me-1 text-warning"></i> contact@gochoaxinh.com
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url('/') }}"><i class="fas fa-home me-1"></i> Trang chủ</a>
                    <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i> Đăng nhập</a>
                    <a href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i> Đăng ký</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo Góc Hoa Xinh -->
                <div class="col-8 col-md-4">
                    <a href="{{ url('/') }}" class="header-logo" title="Góc Hoa Xinh - Shop Hoa Tươi">
                        <img src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/logo.png?1757405855081" alt="Góc Hoa Xinh - Shop Hoa Tươi" class="img-fluid">
                    </a>
                </div>

                <!-- Hotline -->
                <div class="col-md-5 d-none d-md-flex justify-content-center">
                    <a href="tel:0898449950" class="header-hotline">
                        <div class="icon-circle">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <div class="phone-title">Hỗ trợ đặt hoa 24/7</div>
                            <div class="phone-number">0898 449 950</div>
                        </div>
                    </a>
                </div>

                <!-- Actions -->
                <div class="col-4 col-md-3 text-end">
                    <div class="d-inline-flex align-items-center gap-2">
                        @auth
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : url('/dashboard') }}" class="btn btn-outline-success btn-sm fw-bold">
                                <i class="fas fa-user me-1"></i> {{ auth()->user()->name }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-success btn-sm fw-bold me-1">
                                Đăng nhập
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-success btn-sm fw-bold d-none d-sm-inline-block" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                                Đăng ký
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Menu (Góc Hoa Xinh standard) -->
    <nav class="navbar navbar-expand-lg header-menu p-0">
        <div class="container">
            <button class="navbar-toggler my-2 text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#guestNav" aria-controls="guestNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i> Menu
            </button>
            <div class="collapse navbar-collapse" id="guestNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/products') }}">Tất cả sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/categories/bo-hoa-tuoi') }}">Bó Hoa Tươi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/categories/ke-chuc-mung') }}">Kệ Chúc Mừng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/categories/hoa-cuoi') }}">Hoa Cưới</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/posts') }}">Tin Tức</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/contact') }}">Liên Hệ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="bodywrap">
        {{ $slot }}
    </main>

    <!-- Footer (Ported from ShopFlower _Footer.cshtml) -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-logo mb-3">
                        <img src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/logo_footer.png?1757405855081" alt="Góc Hoa Xinh">
                    </div>
                    <p class="small text-white-50">
                        Góc Hoa Xinh chuyên cung cấp các mẫu hoa tươi nghệ thuật, hoa sinh nhật, hoa khai trương, hoa cưới cầm tay sang trọng với thông điệp trao gửi yêu thương trọn vẹn nhất.
                    </p>
                    <div class="small">
                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-warning"></i> 140 Lê Trọng Tấn, Quận Tân Phú, TP. HCM</p>
                        <p class="mb-1"><i class="fas fa-phone-alt me-2 text-warning"></i> Hotline: <strong class="text-white">0898 449 950</strong></p>
                        <p class="mb-0"><i class="fas fa-envelope me-2 text-warning"></i> Email: 2001230486@huit.edu.vn</p>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h5>Danh mục</h5>
                    <ul>
                        <li><a href="{{ url('/') }}">Trang chủ</a></li>
                        <li><a href="{{ url('/products') }}">Tất cả sản phẩm</a></li>
                        <li><a href="{{ url('/categories/bo-hoa-tuoi') }}">Bó hoa tươi</a></li>
                        <li><a href="{{ url('/categories/ke-chuc-mung') }}">Kệ hoa mừng</a></li>
                        <li><a href="{{ url('/categories/hoa-cuoi') }}">Hoa cưới cầm tay</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-6">
                    <h5>Chính sách</h5>
                    <ul>
                        <li><a href="#">Chính sách giao hoa tươi</a></li>
                        <li><a href="#">Hình thức thanh toán</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Điều khoản dịch vụ</a></li>
                        <li><a href="#">Chính sách đổi trả hoa</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5>Kết nối với chúng tôi</h5>
                    <p class="small text-white-50">Nhận thông tin ưu đãi và các bộ sưu tập hoa mới nhất.</p>
                    <div class="d-flex gap-2 mb-3">
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom text-center">
            <div class="container">
                <p class="mb-0">© 2026 Góc Hoa Xinh - Bản quyền thuộc về Nhóm Đồ Án Shop Hoa Tươi Laravel.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
