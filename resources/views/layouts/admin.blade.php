<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - Shop Hoa Tươi</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --admin-primary: #e83e8c;
            --admin-primary-dark: #d63384;
            --admin-sidebar-bg: #1e293b;
            --admin-sidebar-hover: #334155;
            --admin-sidebar-active: #e83e8c;
            --admin-sidebar-text: #94a3b8;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: var(--admin-sidebar-bg);
            min-height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
        }
        #sidebar .brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        #sidebar .brand i {
            color: var(--admin-primary);
            font-size: 1.4rem;
        }
        #sidebar .nav-link {
            color: var(--admin-sidebar-text);
            padding: 0.75rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            margin: 3px 12px;
            transition: all 0.2s;
            text-decoration: none;
        }
        #sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--admin-sidebar-hover);
        }
        #sidebar .nav-link.active {
            color: #fff;
            background-color: var(--admin-primary);
        }
        #sidebar .nav-link i {
            font-size: 1.15rem;
            width: 22px;
            text-align: center;
        }
        .admin-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .admin-header {
            background: #fff;
            padding: 0.85rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-content {
            padding: 1.75rem;
            flex-grow: 1;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: -260px;
                position: fixed;
                top: 0;
                bottom: 0;
            }
            #sidebar.show {
                margin-left: 0;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="d-flex flex-column">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <i class="bi bi-flower1"></i>
                <span>Shop Hoa Tươi</span>
            </a>
            
            <div class="p-2 flex-grow-1">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin/categories') }}" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span>Danh mục hoa</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin/products') }}" class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                            <i class="bi bi-flower3"></i>
                            <span>Sản phẩm hoa</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin/orders') }}" class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i>
                            <span>Đơn hàng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin/posts') }}" class="nav-link {{ request()->is('admin/posts*') ? 'active' : '' }}">
                            <i class="bi bi-newspaper"></i>
                            <span>Tin tức & Bài viết</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Người dùng</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="p-3 border-top border-secondary">
                <a href="{{ url('/') }}" target="_blank" class="nav-link text-white-50 p-2">
                    <i class="bi bi-box-arrow-up-right me-2"></i> Xem website
                </a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <span class="badge bg-pink text-white d-none d-sm-inline" style="background-color: var(--admin-primary);">
                        <i class="bi bi-shield-lock-fill me-1"></i> Admin Portal
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-secondary"></i>
                            <span class="fw-semibold">{{ Auth::user()->name ?? 'Admin' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i> Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Alerts / Toast Messages -->
            <div class="px-4 pt-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <strong>Vui lòng kiểm tra lại:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="admin-content">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
                © {{ date('Y') }} Shop Hoa Tươi - Hệ thống quản trị bán hàng trực tuyến
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');

        if (toggle) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
