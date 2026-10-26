<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#537453">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Icon -->
    <link rel="icon" href="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/favicon.png?1757405855081" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <!-- Bootstrap JS (bundle đã gồm Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style_layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/loading_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ajax-notification.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout_custom.css') }}">
    @stack('styles')

    <!-- Font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- JS -->
    <script src="{{ asset('js/main.js') }}"></script>

    <title>@yield('title', 'Shop Hoa Tươi Góc Hoa Xinh - Những Bó Hoa Tươi xinh xắn')</title>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
            <p class="loading-text">Đang tải trang...</p>
        </div>
    </div>

    <!-- Notification Container for flash messages -->
    @if (session('error'))
        <div class="ajax-notification error" style="display: block;" id="temp-notification">
            {{ session('error') }}
        </div>
        <script>
            setTimeout(function () {
                document.getElementById('temp-notification').style.display = 'none';
            }, 5000);
        </script>
    @endif

    @if (session('success'))
        <div class="ajax-notification success" style="display: block;" id="temp-success-notification">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(function () {
                document.getElementById('temp-success-notification').style.display = 'none';
            }, 3000);
        </script>
    @endif

    <!-- Top banner -->
    <div class="top-banner">
        <div class="container">
            Miễn phí giao hoa nội thành TP.HCM<span class="d-none d-sm-inline"> &ndash; Giao nhanh trong 2 giờ</span>
        </div>
    </div>
    <!-- Header -->
    @include('layouts.partials.header')
    <!-- Body -->
    <div class="bodywrap account-animate">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>
    <!-- Footer -->
    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
