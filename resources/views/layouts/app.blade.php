<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#537453">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/favicon.png?1757405855081" type="image/x-icon">

    <!-- Bootstrap CSS 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <!-- Bootstrap JS Bundle (kèm Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CSS ported từ codebase C# Shop-Flower -->
    <link rel="stylesheet" href="{{ asset('css/style_layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/collection_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trangchu_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contact_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/404page.css') }}">
    <link rel="stylesheet" href="{{ asset('css/product_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/blog_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/loading_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cart_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/wishlist_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ajax-notification.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout_custom.css') }}">
    @stack('styles')

    <!-- JS ported từ codebase C# Shop-Flower -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/wishlist-ajax.js') }}"></script>
    <script src="{{ asset('js/search-realtime.js') }}"></script>

    <title>@yield('title', 'Shop Hoa Tươi Góc Hoa Xinh - Những Bó Hoa Tươi xinh xắn')</title>
</head>
<body>
    <!-- Loading Overlay ported từ _LayoutChung.cshtml -->
    <div id="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
            <p class="loading-text">Đang tải trang...</p>
        </div>
    </div>

    <!-- Notification Toast Container ported từ TempData Shop-Flower -->
    @if (session('error') || session('status_error'))
        <div class="ajax-notification error" style="display: block;" id="temp-notification">
            {{ session('error') ?? session('status_error') }}
        </div>
        <script>
            setTimeout(function () {
                var el = document.getElementById('temp-notification');
                if (el) el.style.display = 'none';
            }, 5000);
        </script>
    @endif

    @if (session('success') || session('status'))
        <div class="ajax-notification success" style="display: block;" id="temp-success-notification">
            {{ session('success') ?? session('status') }}
        </div>
        <script>
            setTimeout(function () {
                var el = document.getElementById('temp-success-notification');
                if (el) el.style.display = 'none';
            }, 3000);
        </script>
    @endif

    <!-- Top Banner -->
    <div class="top-banner">
        <div class="container">
            Miễn phí giao hoa nội thành TP.HCM<span class="d-none d-sm-inline"> &ndash; Giao nhanh trong 2 giờ</span>
        </div>
    </div>

    <!-- Header ported từ _Header.cshtml -->
    @include('layouts.partials.header')

    <!-- Body Wrapper ported từ _LayoutChung.cshtml -->
    <div class="bodywrap account-animate">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>

    <!-- Footer ported từ _Footer.cshtml -->
    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
