<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - Góc Hoa Xinh</title>
    <link rel="icon" href="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/favicon.png?1757405855081" type="image/x-icon">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans & Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --brand-primary: #537453;
            --brand-primary-dark: #3e593e;
            --brand-primary-light: #6a916a;
            --brand-accent: #ea8725;
            --brand-pink: #e83e8c;
            --admin-sidebar-bg: #1e3926;
            --admin-sidebar-active: #537453;
            --admin-sidebar-hover: #294c34;
            --admin-sidebar-text: #d1e2d4;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f6f8;
            min-height: 100vh;
            color: #2c3e50;
            overflow-x: hidden;
        }

        /* Sidebar Styles (Ported from C# Dashboard.css) */
        #sidebar {
            width: 260px;
            min-width: 260px;
            max-width: 260px;
            background: linear-gradient(180deg, #1b3524 0%, #254832 100%);
            color: #fff;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        #sidebar.collapsed {
            margin-left: -260px;
        }

        #sidebar .sidebar-header {
            padding: 1.25rem 1.5rem;
            background: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #sidebar .sidebar-header .brand-icon {
            width: 40px;
            height: 40px;
            background: #fff;
            color: var(--brand-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        #sidebar .sidebar-header .brand-text {
            line-height: 1.2;
        }

        #sidebar .sidebar-header .brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            font-family: 'Nunito', sans-serif;
            letter-spacing: 0.5px;
        }

        #sidebar .sidebar-header .brand-badge {
            font-size: 0.7rem;
            background: rgba(255, 255, 255, 0.15);
            color: #d1e2d4;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 700;
        }

        #sidebar .nav-list {
            padding: 1rem 0.75rem;
            list-style: none;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        #sidebar .nav-item {
            margin-bottom: 4px;
        }

        #sidebar .nav-link {
            color: var(--admin-sidebar-text);
            padding: 0.75rem 1rem;
            font-size: 0.92rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        #sidebar .nav-link i {
            font-size: 1.15rem;
            width: 24px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        #sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--admin-sidebar-hover);
            transform: translateX(3px);
        }

        #sidebar .nav-link.active {
            color: #fff;
            background-color: var(--brand-primary);
            box-shadow: 0 4px 10px rgba(83, 116, 83, 0.4);
        }

        #sidebar .nav-link.active i {
            color: #ffdf7e;
        }

        #sidebar .sidebar-footer {
            padding: 1rem 1.25rem;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Main Content Wrapper */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admin-main.expanded {
            margin-left: 0;
        }

        /* Top Header Navbar */
        .admin-header {
            background: #fff;
            height: 68px;
            padding: 0 1.75rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .admin-content {
            padding: 1.75rem;
            flex-grow: 1;
        }

        /* Stats Cards (Ported from C# Dashboard) */
        .card-stat {
            background-color: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .card-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
        }

        .card-stat .icon-box {
            font-size: 26px;
            width: 58px;
            height: 58px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            flex-shrink: 0;
            color: #fff;
        }

        .card-stat .stat-content {
            flex-grow: 1;
        }

        .card-stat .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a202c;
            line-height: 1.2;
            margin-bottom: 3px;
        }

        .card-stat .stat-title {
            color: #718096;
            font-size: 0.88rem;
            font-weight: 500;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: -260px;
            }
            #sidebar.show {
                margin-left: 0;
            }
            .admin-main {
                margin-left: 0;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Backdrop -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar">
        <div class="sidebar-header">
            <div class="brand-icon">
                <i class="fas fa-spa text-success"></i>
            </div>
            <div class="brand-text">
                <div class="brand-title">GÓC HOA XINH</div>
                <span class="brand-badge"><i class="fas fa-crown text-warning me-1"></i>ADMIN PANEL</span>
            </div>
        </div>

        <ul class="nav-list">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Quản lý danh mục</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/products') }}" class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i>
                    <span>Quản lý sản phẩm</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/orders') }}" class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Quản lý hóa đơn</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/posts') }}" class="nav-link {{ request()->is('admin/posts*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper"></i>
                    <span>Quản lý tin tức</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/contacts') }}" class="nav-link {{ request()->is('admin/contacts*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i>
                    <span>Danh sách liên hệ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>Quản lý tài khoản</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-external-link-alt"></i>
                <span>Xem Website Góc Hoa Xinh</span>
            </a>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="admin-main" id="adminMain">
        <!-- Top Navbar -->
        <header class="admin-header">
            <div class="d-flex align-items-center gap-3">
                <button type="button" id="sidebarCollapse" class="btn btn-light border shadow-sm" title="Thu gọn / Mở rộng Sidebar">
                    <i class="fas fa-bars text-secondary"></i>
                </button>
                <div class="d-none d-md-flex align-items-center gap-2 text-muted small">
                    <i class="fas fa-home"></i>
                    <span>/</span>
                    <span class="text-dark fw-medium">@yield('title', 'Quản trị')</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ url('/') }}" target="_blank" class="btn btn-sm btn-outline-success d-none d-sm-inline-flex align-items-center gap-1" style="border-color: var(--brand-primary); color: var(--brand-primary);">
                    <i class="fas fa-store"></i>
                    <span>Cửa hàng</span>
                </a>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light border shadow-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fs-5 text-success"></i>
                        <span class="fw-bold text-dark">{{ Auth::user()->name ?? 'Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="adminUserDropdown">
                        <li>
                            <div class="dropdown-item-text px-3 py-2 border-bottom">
                                <div class="fw-bold">{{ Auth::user()->name ?? 'Administrator' }}</div>
                                <div class="small text-muted">{{ Auth::user()->email ?? 'admin@shophoatuoi.com' }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-shield me-2 text-primary"></i> Hồ sơ tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ url('/') }}" target="_blank">
                                <i class="fas fa-globe me-2 text-success"></i> Xem trang chủ
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle fs-5 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- Body Content -->
        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const adminMain = document.getElementById('adminMain');
            const toggleBtn = document.getElementById('sidebarCollapse');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        sidebar.classList.toggle('show');
                        overlay.classList.toggle('show');
                    } else {
                        sidebar.classList.toggle('collapsed');
                        adminMain.classList.toggle('expanded');
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
        });
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
