<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/favicon.png?1757405855081" type="image/x-icon">

    <title>@yield('title', 'Dashboard') - Admin</title>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Bootstrap 4.5.2 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 5.15.3 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <!-- Dashboard.css (Ported from C# ASP.NET MVC 5 ShopFlower) -->
    <link href="{{ asset('css/Dashboard.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/PagedList.css') }}" rel="stylesheet" />
    <!-- Toast notification styles (giống khi thêm vào giỏ hàng) -->
    <link href="{{ asset('css/ajax-notification.css') }}" rel="stylesheet" />
    <style>
        /* Smooth responsive adjustments */
        .sidebar {
            overflow-y: auto;
        }
        .pagination {
            margin-bottom: 0;
        }
        .table th {
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
    @stack('styles')
    @yield('styles')
</head>
<body>
    <!-- Floating Toast Notification (giống khi thêm vào giỏ hàng) -->
    @if (session('error') || session('status_error'))
        <div class="ajax-notification cart-toast error" style="display: block;" id="admin-toast-error">
            {{ session('error') ?? session('status_error') }}
        </div>
        <script>
            setTimeout(function () {
                var el = document.getElementById('admin-toast-error');
                if (el) {
                    el.style.transition = 'opacity 0.3s ease';
                    el.style.opacity = '0';
                    setTimeout(function () { if (el && el.parentNode) el.parentNode.removeChild(el); }, 300);
                }
            }, 4000);
        </script>
    @endif

    @if (session('success') || session('status'))
        <div class="ajax-notification cart-toast success" style="display: block;" id="admin-toast-success">
            {{ session('success') ?: session('status') }}
        </div>
        <script>
            setTimeout(function () {
                var el = document.getElementById('admin-toast-success');
                if (el) {
                    el.style.transition = 'opacity 0.3s ease';
                    el.style.opacity = '0';
                    setTimeout(function () { if (el && el.parentNode) el.parentNode.removeChild(el); }, 300);
                }
            }, 2800);
        </script>
    @endif

    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-align:center; color:wheat; margin:0; font-weight:700; letter-spacing:1px;">ADMIN</h3>
        </div>
        <ul class="list-unstyled components">
            <li class="{{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="{{ request()->is('admin/products*') ? 'active' : '' }}">
                <a href="{{ route('admin.products.index') }}"><i class="fas fa-box"></i> Quản lý sản phẩm</a>
            </li>
            <li class="{{ request()->is('admin/orders*') ? 'active' : '' }}">
                <a href="{{ route('admin.orders.index') }}"><i class="fas fa-file-invoice"></i> Quản lý hóa đơn</a>
            </li>
            <li class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}"><i class="fas fa-users"></i> Quản lý tài khoản</a>
            </li>
            <li class="{{ request()->is('admin/posts*') ? 'active' : '' }}">
                <a href="{{ route('admin.posts.index') }}"><i class="fas fa-newspaper"></i> Quản lý tin tức</a>
            </li>
            <li class="{{ request()->is('admin/contacts*') ? 'active' : '' }}">
                <a href="{{ route('admin.contacts.index') }}"><i class="fas fa-address-book"></i> Danh sách liên hệ</a>
            </li>
            <li class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
                <a href="{{ route('admin.categories.index') }}"><i class="fas fa-tags"></i> Quản lý danh mục</a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="fas fa-align-left"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ml-auto align-items-center">
                        <li class="nav-item mr-3">
                            <a class="btn btn-outline-success btn-sm" href="{{ url('/') }}" target="_blank">
                                <i class="fas fa-store"></i> Xem Website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" title="Thông báo">
                                <i class="fas fa-bell"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <div class="dropdown">
                                <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-user"></i> {{ Auth::user()->name ?? 'Admin' }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                    <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display:inline">
                                        @csrf
                                        <a class="dropdown-item text-danger" href="#" onclick="document.getElementById('logoutForm').submit(); return false;">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="content-body">
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @stack('modals')

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('.sidebar').toggleClass('active');
                $('.main-content').toggleClass('active');
            });
            if ($.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip();
            }

            // Đóng nhanh thông báo nổi khi click vào
            $(document).on('click', '.ajax-notification', function () {
                $(this).fadeOut(200, function () {
                    $(this).remove();
                });
            });
        });

        // Hàm hiển thị thông báo toast dùng chung trong Admin
        function showNotification(message, type) {
            type = type || 'success';
            $('.ajax-notification.cart-toast').remove();

            var box = $('<div class="ajax-notification cart-toast ' + type + '" style="display: block;">' + message + '</div>');
            $('body').append(box);

            setTimeout(function () {
                box.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 2800);
        }
        window.showNotification = showNotification;
        window.showCartNotification = showNotification;
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
