@php
    $cartCount = 0;
    if (auth()->check()) {
        try {
            $userCart = app(\App\Services\SalesService::class)->cart(auth()->user());
            if ($userCart && is_array($userCart->items)) {
                $cartCount = array_sum(array_column($userCart->items, 'quantity'));
            }
        } catch (\Throwable $e) {
            try {
                $uid = auth()->id();
                $ids = [(string) $uid];
                if (\MongoDB\BSON\ObjectId::isValid($uid)) {
                    $ids[] = new \MongoDB\BSON\ObjectId($uid);
                }
                $userCart = \App\Models\Cart::whereIn('user_id', $ids)->first();
                if ($userCart && is_array($userCart->items)) {
                    $cartCount = array_sum(array_column($userCart->items, 'quantity'));
                }
            } catch (\Throwable $e2) {}
        }
    }
    $wishlistCount = 0;
    try {
        $wishlistCount = count(\App\Models\Wishlist::productCodesFor(auth()->user()));
    } catch (\Throwable $e) {}
@endphp
<header class="header">
    <div class="main-header">
        <div class="container">
            <div class="box-header">
                <div class="row align-items-center">
                    <!-- Logo -->
                    <div class="col-8 col-lg-3 col-md-8 header-logo">
                        <button class="menu-icon md-hidden" aria-label="Menu" id="btn-menu-mobile" title="Menu">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"></path>
                            </svg>
                        </button>

                        <a href="/" class="logo-wrapper" title="Góc Hoa Xinh">
                            <img width="249" height="26" src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/logo.png?1757405855081" alt="Góc Hoa Xinh" class="lazyload loaded">
                        </a>
                    </div>

                    <!-- Search Realtime Form ported từ Shop-Flower -->
                    <div class="col-12 col-md-12 col-lg-4 header-mid">
                        <div class="list-top-item header_tim_kiem">
                            <form action="/products" method="get" class="header-search-form input-group search-bar position-relative" role="search" data-product-search data-endpoint="/api/products/search">
                                <input name="q" value="{{ request('q') }}" required="" class="input-group-field auto-search search-auto form-control" placeholder="Tìm sản phẩm..." autocomplete="off" type="text">
                                <input type="hidden" name="type" value="product">
                                <button type="submit" class="btn icon-fallback-text" aria-label="Tìm kiếm" title="Tìm kiếm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"></path>
                                    </svg>
                                </button>
                                <!-- Popup gợi ý kết quả Live Search Realtime -->
                                <div data-search-results hidden class="search-ajax-results position-absolute bg-white border rounded shadow-sm w-100 mt-1" style="z-index: 1050; top: 100%; left: 0; max-height: 350px; overflow-y: auto;"></div>
                            </form>
                        </div>
                    </div>

                    <!-- Info & Actions (Hotline, Account, Wishlist, Cart) -->
                    <div class="col-4 col-lg-5 col-md-4 header-right">
                        <!-- Hotline -->
                        <div class="sudes-header-hotline sm-hidden">
                            <a href="tel:0898449950" title="Điện thoại: 0898449950">
                                <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_hotline)">
                                        <path d="M18.1771 14.2476C17.7063 13.7573 17.1383 13.4952 16.5364 13.4952C15.9393 13.4952 15.3665 13.7525 14.8762 14.2428L13.3422 15.7719C13.216 15.7039 13.0898 15.6408 12.9685 15.5777C12.7937 15.4903 12.6287 15.4078 12.4879 15.3204C11.051 14.4078 9.74519 13.2185 8.49278 11.6797C7.88599 10.9127 7.47823 10.2671 7.18212 9.61177C7.58017 9.2477 7.9491 8.86906 8.30832 8.50499C8.44424 8.36907 8.58016 8.22829 8.71608 8.09237C9.73548 7.07297 9.73548 5.7526 8.71608 4.73319L7.39085 3.40797C7.24037 3.25748 7.08503 3.10214 6.9394 2.94681C6.64814 2.64584 6.34232 2.33516 6.02679 2.04391C5.55592 1.57789 4.99282 1.33032 4.4006 1.33032C3.80837 1.33032 3.23556 1.57789 2.75013 2.04391C2.74528 2.04876 2.74528 2.04876 2.74043 2.05361L1.08996 3.71864C0.46861 4.33999 0.114245 5.09726 0.0365763 5.97589C-0.0799271 7.39335 0.337543 8.71372 0.657928 9.57779C1.44433 11.6991 2.61907 13.6651 4.37147 15.7719C6.49766 18.3107 9.05588 20.3155 11.9782 21.7281C13.0947 22.2572 14.5849 22.8834 16.25 22.9902C16.3519 22.9951 16.4587 22.9999 16.5558 22.9999C17.6771 22.9999 18.6189 22.597 19.3567 21.7961C19.3616 21.7864 19.3713 21.7815 19.3761 21.7718C19.6286 21.466 19.9198 21.1893 20.2256 20.8932C20.4344 20.6942 20.648 20.4854 20.8567 20.267C21.3373 19.767 21.5897 19.1845 21.5897 18.5874C21.5897 17.9855 21.3324 17.4078 20.8421 16.9224L18.1771 14.2476Z" fill="white"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_hotline">
                                            <rect width="23" height="23" fill="white"></rect>
                                        </clipPath>
                                    </defs>
                                </svg>
                                <div class="text-box">
                                    <span class="acc-text-small">Gọi mua hàng</span>
                                    <span class="acc-text">0898449950</span>
                                </div>
                            </a>
                        </div>

                        <!-- Account Dropdown ported từ Shop-Flower -->
                        <div class="sudes-header-account header-action_account">
                            @auth
                                @php
                                    $accountUrl = auth()->user()->isAdmin() ? '/admin/dashboard' : route('profile.edit', [], false);
                                @endphp
                                <a href="{{ $accountUrl }}" class="header-account" aria-label="Tài khoản" title="Tài khoản">
                                    <div class="text-box">
                                        <span class="acc-text-small sm-hidden">Thông tin</span>
                                        <span class="acc-text">
                                            {{ auth()->user()->name }}
                                            <svg width="12" height="12" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg); margin-left: 4px;">
                                                <path d="M7.13382 7.1278L0.512271 0.509686L7.13382 7.1278ZM7.13382 7.1278C7.62239 7.61603 7.622 8.40641 7.13301 8.89414L0.502368 15.5089L7.13055 8.89499C7.61976 8.407 7.62011 7.61541 7.13138 7.12699L7.13382 7.1278Z" stroke="white" stroke-width="2"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </a>

                                <ul>
                                    @if (auth()->user()->isAdmin())
                                        <li class="li-account">
                                            <a rel="nofollow" href="/admin/dashboard" title="Trang quản trị"><i class="fa fa-dashboard me-2"></i>Quản trị hệ thống</a>
                                        </li>
                                    @endif
                                    <li class="li-account">
                                        <a rel="nofollow" href="{{ route('profile.edit', [], false) }}" title="Thông tin tài khoản"><i class="fa fa-user me-2"></i>Thông tin tài khoản</a>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="/account/orders" title="Lịch sử đơn hàng"><i class="fa fa-shopping-bag me-2"></i>Lịch sử đơn hàng</a>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="/wishlist" title="Danh sách yêu thích">
                                            <i class="fa fa-heart me-2 text-danger"></i>Yêu thích (<span class="js-wishlist-count">{{ $wishlistCount }}</span>)
                                        </a>
                                    </li>
                                    <li class="li-account">
                                        <form method="post" action="{{ route('logout', [], false) }}" style="display:inline" id="logoutForm">
                                            @csrf
                                            <a href="#" rel="nofollow" title="Đăng xuất" onclick="document.getElementById('logoutForm').submit(); return false;">
                                                <i class="fa fa-sign-out me-2"></i>Đăng xuất
                                            </a>
                                        </form>
                                    </li>
                                </ul>
                            @else
                                <a href="{{ route('login', [], false) }}" class="header-account" aria-label="Tài khoản" title="Tài khoản">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"></path>
                                    </svg>
                                    <div class="text-box">
                                        <span class="acc-text-small sm-hidden">Thông tin</span>
                                        <span class="acc-text">
                                            Tài khoản
                                            <svg width="12" height="12" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg); margin-left: 4px;">
                                                <path d="M7.13382 7.1278L0.512271 0.509686L7.13382 7.1278ZM7.13382 7.1278C7.62239 7.61603 7.622 8.40641 7.13301 8.89414L0.502368 15.5089L7.13055 8.89499C7.61976 8.407 7.62011 7.61541 7.13138 7.12699L7.13382 7.1278Z" stroke="white" stroke-width="2"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                                <ul>
                                    <li class="li-account">
                                        <a rel="nofollow" href="{{ route('login', [], false) }}" title="Đăng nhập"><i class="fa fa-sign-in me-2"></i>Đăng nhập</a>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="{{ route('register', [], false) }}" title="Đăng ký"><i class="fa fa-user-plus me-2"></i>Đăng ký</a>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="/wishlist" title="Danh sách yêu thích">
                                            <i class="fa fa-heart me-2 text-danger"></i>Yêu thích (<span class="js-wishlist-count">{{ $wishlistCount }}</span>)
                                        </a>
                                    </li>
                                </ul>
                            @endauth
                        </div>

                        <!-- Wishlist Header Icon -->
                        <div class="sudes-header-cart header-action_wishlist">
                            <a class="a-hea" href="/wishlist" aria-label="Yêu thích" title="Yêu thích">
                                <span class="box-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
                                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"></path>
                                    </svg>
                                </span>
                                <span class="count_item count_item_pr js-wishlist-count">{{ $wishlistCount }}</span>
                            </a>
                        </div>

                        <!-- Cart Header Icon -->
                        <div class="sudes-header-cart header-action_cart">
                            <a class="a-hea" href="/cart" aria-label="Giỏ hàng" title="Giỏ hàng">
                                <span class="box-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"></path>
                                    </svg>
                                </span>
                                <span class="count_item count_item_pr js-cart-count">{{ $cartCount }}</span>
                                <span class="item-title sm-hidden">Giỏ hàng</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Menu & Mobile Drawer -->
    <div class="header-menu">
        <div class="container p-0">
            <!-- Header của Drawer chỉ hiện trên Mobile/Tablet -->
            <div class="drawer-user-header d-lg-none">
                <button type="button" class="drawer-close-btn" id="btn-close-drawer" aria-label="Đóng menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                @auth
                    <div class="drawer-user-avatar">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1, 'UTF-8')) }}
                    </div>
                    <div class="drawer-user-name">{{ auth()->user()->name }}</div>
                    <div class="drawer-user-sub">{{ auth()->user()->email ?? 'Khách hàng thân thiết' }}</div>
                @else
                    <div class="drawer-user-avatar">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="drawer-user-name">Xin chào quý khách!</div>
                    <div class="drawer-user-sub">Đăng nhập để nhận ưu đãi thành viên</div>
                    <div class="drawer-auth-actions">
                        <a href="{{ route('login') }}" class="drawer-auth-btn btn-login">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="drawer-auth-btn btn-register">Đăng ký</a>
                    </div>
                @endauth
            </div>

            <div class="navigation-horizontal overflow">
                <div class="col-menu">
                    <ul id="nav" class="nav">
                        <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                            <a class="nav-link" href="/" title="Trang chủ">Trang chủ</a>
                        </li>
                        <li class="nav-item {{ request()->is('products') && !request('category_slug') ? 'active' : '' }}">
                            <a class="nav-link" href="/products" title="Tất cả sản phẩm">Tất cả sản phẩm</a>
                        </li>
                        <li class="nav-item {{ request('category_slug') == 'bo-hoa-tuoi' ? 'active' : '' }}">
                            <a class="nav-link" href="/products?category_slug=bo-hoa-tuoi" title="Bó Hoa Tươi">Bó Hoa Tươi</a>
                        </li>
                        <li class="nav-item has-childs {{ in_array(request('category_slug'), ['ke-chuc-mung', 'gio-hoa-chuc-mung']) ? 'active' : '' }}">
                            <a href="/products?category_slug=ke-chuc-mung" class="nav-link" title="Kệ Hoa chúc mừng">
                                Kệ Hoa chúc mừng
                                <svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.13382 7.1278L0.512271 0.509686L7.13382 7.1278ZM7.13382 7.1278C7.62239 7.61603 7.622 8.40641 7.13301 8.89414L0.502368 15.5089L7.13055 8.89499C7.61976 8.407 7.62011 7.61541 7.13138 7.12699L7.13382 7.1278Z"></path>
                                </svg>
                            </a>
                            <i class="open_mnu down_icon"></i>
                            <ul class="dropdown-menu">
                                <li class="nav-item-lv2">
                                    <a class="nav-link" href="/products?category_slug=ke-chuc-mung" title="Kệ chúc mừng">Kệ chúc mừng</a>
                                </li>
                                <li class="nav-item-lv2">
                                    <a class="nav-link" href="/products?category_slug=gio-hoa-chuc-mung" title="Giỏ hoa chúc mừng">Giỏ hoa chúc mừng</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ request('category_slug') == 'hoa-cuoi' ? 'active' : '' }}">
                            <a class="nav-link" href="/products?category_slug=hoa-cuoi" title="Hoa Cưới">Hoa Cưới</a>
                        </li>
                        <li class="nav-item {{ request('category_slug') == 'hoa-sap' ? 'active' : '' }}">
                            <a class="nav-link" href="/products?category_slug=hoa-sap" title="Hoa Sáp">Hoa Sáp</a>
                        </li>
                        <li class="nav-item {{ request()->is('posts*') ? 'active' : '' }}">
                            <a class="nav-link" href="/posts" title="Tin Tức">Tin Tức</a>
                        </li>
                        <li class="nav-item {{ request()->is('contact*') ? 'active' : '' }}">
                            <a class="nav-link" href="/contact" title="Liên Hệ">Liên Hệ</a>
                        </li>
                    </ul>

                    <!-- Khối menu tài khoản ở chân Drawer Mobile -->
                    <div class="drawer-account-section d-lg-none">
                        @auth
                            @if (auth()->user()->isAdmin())
                                <a href="/admin/dashboard" class="drawer-menu-item text-primary">
                                    <i class="fa-solid fa-gauge"></i>
                                    <span>Quản trị hệ thống</span>
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="drawer-menu-item">
                                <i class="fa-regular fa-id-badge text-success"></i>
                                <span>Thông tin tài khoản</span>
                            </a>
                            <a href="{{ route('orders.index') }}" class="drawer-menu-item">
                                <i class="fa-solid fa-receipt text-warning"></i>
                                <span>Đơn hàng của tôi</span>
                            </a>
                            <a href="{{ route('wishlist.index') }}" class="drawer-menu-item">
                                <i class="fa-solid fa-heart text-danger"></i>
                                <span>Yêu thích (<span class="js-wishlist-count">{{ $wishlistCount }}</span>)</span>
                            </a>
                            <a href="tel:0898449950" class="drawer-menu-item">
                                <i class="fa-solid fa-phone text-info"></i>
                                <span>Hotline: 0898.449.950</span>
                            </a>
                            <form method="post" action="{{ route('logout') }}" id="drawerLogoutForm" class="m-0">
                                @csrf
                                <a href="#" class="drawer-menu-item text-danger" onclick="document.getElementById('drawerLogoutForm').submit(); return false;">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>Đăng xuất</span>
                                </a>
                            </form>
                        @else
                            <a href="{{ route('wishlist.index') }}" class="drawer-menu-item">
                                <i class="fa-solid fa-heart text-danger"></i>
                                <span>Yêu thích (<span class="js-wishlist-count">{{ $wishlistCount }}</span>)</span>
                            </a>
                            <a href="tel:0898449950" class="drawer-menu-item">
                                <i class="fa-solid fa-phone text-info"></i>
                                <span>Hotline: 0898.449.950</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-nav-overflow"></div>
</header>
