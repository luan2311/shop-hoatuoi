@php
    // Số trên icon giỏ hàng (Member 3) sẽ nối với MongoDB sau
    $cartCount = 0;
    $wishlistCount = count(\App\Models\Wishlist::productCodesFor(auth()->user()));
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
                            <img width="249" height="26" src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/logo.png?1757405855081" data-src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/logo.png?1757405855081" alt="Góc Hoa Xinh" class="lazyload loaded" data-was-processed="true">
                        </a>
                    </div>
                    <!-- Search -->
                    <div class="col-12 col-md-12 col-lg-4 header-mid">
                        <div class="list-top-item header_tim_kiem">
                            <form action="/products" method="get" class="header-search-form input-group search-bar" role="search">
                                <input name="q" required="" class="input-group-field auto-search search-auto form-control" placeholder="Tìm sản phẩm..." autocomplete="off" type="text">
                                <input type="hidden" name="type" value="product">
                                <button type="submit" class="btn icon-fallback-text" aria-label="Tìm kiếm" title="Tìm kiếm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"></path>
                                    </svg>
                                </button>

                            </form>
                        </div>
                    </div>
                    <!-- Info -->
                    <div class="col-4 col-lg-5 col-md-4 header-right">
                        <div class="sudes-header-hotline sm-hidden">
                            <a title="Điện thoại: 0898449950">
                                <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_509_1081)">
                                        <path d="M18.1771 14.2476C17.7063 13.7573 17.1383 13.4952 16.5364 13.4952C15.9393 13.4952 15.3665 13.7525 14.8762 14.2428L13.3422 15.7719C13.216 15.7039 13.0898 15.6408 12.9685 15.5777C12.7937 15.4903 12.6287 15.4078 12.4879 15.3204C11.051 14.4078 9.74519 13.2185 8.49278 11.6797C7.88599 10.9127 7.47823 10.2671 7.18212 9.61177C7.58017 9.2477 7.9491 8.86906 8.30832 8.50499C8.44424 8.36907 8.58016 8.22829 8.71608 8.09237C9.73548 7.07297 9.73548 5.7526 8.71608 4.73319L7.39085 3.40797C7.24037 3.25748 7.08503 3.10214 6.9394 2.94681C6.64814 2.64584 6.34232 2.33516 6.02679 2.04391C5.55592 1.57789 4.99282 1.33032 4.4006 1.33032C3.80837 1.33032 3.23556 1.57789 2.75013 2.04391C2.74528 2.04876 2.74528 2.04876 2.74043 2.05361L1.08996 3.71864C0.46861 4.33999 0.114245 5.09726 0.0365763 5.97589C-0.0799271 7.39335 0.337543 8.71372 0.657928 9.57779C1.44433 11.6991 2.61907 13.6651 4.37147 15.7719C6.49766 18.3107 9.05588 20.3155 11.9782 21.7281C13.0947 22.2572 14.5849 22.8834 16.25 22.9902C16.3519 22.9951 16.4587 22.9999 16.5558 22.9999C17.6771 22.9999 18.6189 22.597 19.3567 21.7961C19.3616 21.7864 19.3713 21.7815 19.3761 21.7718C19.6286 21.466 19.9198 21.1893 20.2256 20.8932C20.4344 20.6942 20.648 20.4854 20.8567 20.267C21.3373 19.767 21.5897 19.1845 21.5897 18.5874C21.5897 17.9855 21.3324 17.4078 20.8421 16.9224L18.1771 14.2476ZM19.915 19.3592C19.9101 19.3641 19.9101 19.3592 19.915 19.3592C19.7256 19.5631 19.5315 19.7476 19.3227 19.9514C19.0072 20.2524 18.6868 20.5679 18.3859 20.9223C17.8956 21.4466 17.3179 21.6941 16.5606 21.6941C16.4878 21.6941 16.4102 21.6941 16.3373 21.6893C14.8956 21.5971 13.5558 21.034 12.551 20.5534C9.80344 19.2233 7.39085 17.335 5.38602 14.9418C3.7307 12.9467 2.62392 11.102 1.89092 9.12149C1.43947 7.91276 1.27442 6.97103 1.34724 6.08269C1.39578 5.51474 1.61423 5.04387 2.01713 4.64096L3.67245 2.98564C3.91031 2.76234 4.16274 2.64099 4.41031 2.64099C4.71613 2.64099 4.9637 2.82545 5.11904 2.98079C5.12389 2.98564 5.12874 2.9905 5.1336 2.99535C5.42971 3.27205 5.71126 3.55845 6.00737 3.86427C6.15786 4.01961 6.3132 4.17495 6.46853 4.33514L7.79376 5.66036C8.30832 6.17492 8.30832 6.65064 7.79376 7.1652C7.65298 7.30597 7.51706 7.44675 7.37629 7.58267C6.96853 8.00014 6.58018 8.38848 6.15786 8.76712C6.14815 8.77683 6.13844 8.78168 6.13359 8.79139C5.71612 9.20886 5.79378 9.61662 5.88116 9.89332C5.88602 9.90788 5.89087 9.92245 5.89573 9.93701C6.24038 10.7719 6.72581 11.5583 7.46367 12.4952L7.46852 12.5001C8.80831 14.1505 10.2209 15.4369 11.7791 16.4224C11.9782 16.5486 12.1821 16.6505 12.3762 16.7476C12.551 16.835 12.716 16.9175 12.8568 17.0049C12.8762 17.0146 12.8956 17.0292 12.9151 17.0389C13.0801 17.1214 13.2354 17.1602 13.3956 17.1602C13.7985 17.1602 14.051 16.9078 14.1335 16.8253L15.7937 15.1651C15.9587 15.0001 16.2208 14.801 16.5267 14.801C16.8276 14.801 17.0752 14.9903 17.2257 15.1554C17.2305 15.1602 17.2305 15.1602 17.2354 15.1651L19.9101 17.8398C20.4101 18.335 20.4101 18.8447 19.915 19.3592Z" fill="white"></path>
                                        <path d="M12.4296 5.47116C13.7014 5.68475 14.8567 6.28669 15.779 7.209C16.7013 8.13132 17.2984 9.28665 17.5169 10.5585C17.5703 10.8789 17.847 11.1022 18.1625 11.1022C18.2013 11.1022 18.2353 11.0973 18.2741 11.0924C18.6334 11.0342 18.8712 10.6944 18.813 10.3352C18.5508 8.79636 17.8227 7.39347 16.7111 6.28183C15.5994 5.17019 14.1965 4.44205 12.6577 4.17992C12.2985 4.12166 11.9635 4.35953 11.9004 4.71389C11.8373 5.06825 12.0703 5.41291 12.4296 5.47116Z" fill="white"></path>
                                        <path d="M22.9732 10.1458C22.5411 7.61184 21.347 5.30604 19.512 3.47111C17.6771 1.63618 15.3713 0.442024 12.8374 0.00999088C12.483 -0.0531151 12.148 0.1896 12.0849 0.543965C12.0267 0.903183 12.2646 1.23813 12.6238 1.30124C14.8859 1.68473 16.949 2.75753 18.5897 4.39343C20.2305 6.03419 21.2984 8.09727 21.6819 10.3594C21.7353 10.6798 22.012 10.9031 22.3275 10.9031C22.3664 10.9031 22.4003 10.8982 22.4392 10.8933C22.7935 10.84 23.0363 10.5001 22.9732 10.1458Z" fill="white"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_509_1081">
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
                                            <!-- chevron svg -->
                                        </span>
                                    </div>
                                </a>

                                <ul>
                                    <li class="li-account">
                                        <a rel="nofollow" href="{{ $accountUrl }}" title="Tài khoản">Tài khoản</a>
                                    </li>
                                    <li class="li-account">
                                        <form method="post" action="{{ route('logout', [], false) }}" style="display:inline" id="logoutForm">
                                            @csrf
                                            <a href="#" rel="nofollow" title="Đăng xuất" onclick="document.getElementById('logoutForm').submit(); return false;">Đăng xuất</a>
                                        </form>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="/wishlist" title="Danh sách yêu thích">
                                            <!-- heart svg -->
                                            Yêu thích(<span class="js-wishlist-count">{{ $wishlistCount }}</span>)
                                        </a>
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
                                            <svg width="30" height="30" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M7.13382 7.1278L7.13379 7.12777L0.512271 0.509686L0.795057 0.226752L0.51227 0.509685C0.512123 0.509538 0.51201 0.509431 0.511927 0.509356L7.13382 7.1278ZM7.13382 7.1278C7.62239 7.61603 7.622 8.40641 7.13301 8.89414L7.13298 8.89417L0.502368 15.5089C0.50222 15.509 0.502106 15.5091 0.502022 15.5092C0.501841 15.5092 0.501547 15.5093 0.501149 15.5093C0.500827 15.5093 0.500574 15.5093 0.500392 15.5092L7.13055 8.89499C7.13056 8.89498 7.13057 8.89497 7.13058 8.89495C7.61976 8.407 7.62011 7.61541 7.13138 7.12699L7.13382 7.1278Z"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                                <ul>
                                    <li class="li-account">
                                        <a rel="nofollow" href="{{ route('login', [], false) }}" title="Đăng nhập">Đăng nhập</a>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="{{ route('register', [], false) }}" title="Đăng ký">Đăng ký</a>
                                    </li>
                                    <li class="li-account">
                                        <a rel="nofollow" href="/wishlist" title="Danh sách yêu thích">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
                                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"></path>
                                            </svg>
                                            Yêu thích(<span class="js-wishlist-count">{{ $wishlistCount }}</span>)
                                        </a>
                                    </li>
                                </ul>
                            @endauth
                        </div>

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

                        <div class="sudes-header-cart header-action_cart">
                            <a class="a-hea" href="/cart" aria-label="Giỏ hàng" title="Giỏ hàng">
                                <span class="box-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"></path>
                                    </svg>
                                </span>
                                <span class="count_item count_item_pr hidden-count">{{ $cartCount }}</span>
                                <span class="item-title sm-hidden">Giỏ hàng</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Menu -->
    <div class="header-menu">
        <div class="container">
            <div class="navigation-horizontal overflow">
                <div class="title_menu md-hidden">
                    <span class="title_">
                        Danh mục
                    </span>
                </div>
                <div class="col-menu">
                    <ul id="nav" class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/" title="Trang chủ">Trang chủ</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="/products" title="Tất cả sản phẩm">Tất cả sản phẩm</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="/products?category_slug=bo-hoa-tuoi" title="Bó Hoa Tươi">Bó Hoa Tươi</a>
                        </li>
                        <li class="nav-item has-childs  ">
                            <a href="/products?category_slug=ke-chuc-mung" class="nav-link" title="Kệ Hoa chúc mừng">
                                Kệ Hoa chúc mừng
                                <svg width="8" height="17" viewBox="0 0 8 17" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.13382 7.1278L7.13379 7.12777L0.512271 0.509686L0.795057 0.226752L0.51227 0.509685C0.512123 0.509538 0.51201 0.509431 0.511927 0.509356L7.13382 7.1278ZM7.13382 7.1278C7.62239 7.61603 7.622 8.40641 7.13301 8.89414L7.13298 8.89417L0.502368 15.5089C0.50222 15.509 0.502106 15.5091 0.502022 15.5092C0.501841 15.5092 0.501547 15.5093 0.501149 15.5093C0.500827 15.5093 0.500574 15.5093 0.500392 15.5092L7.13055 8.89499C7.13056 8.89498 7.13057 8.89497 7.13058 8.89495C7.61976 8.407 7.62011 7.61541 7.13138 7.12699L7.13382 7.1278Z">
                                    </path>
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

                        <li class="nav-item ">
                            <a class="nav-link" href="/products?category_slug=hoa-cuoi" title="Hoa Cưới">Hoa Cưới</a>
                        </li>

                        <li class="nav-item ">
                            <a class="nav-link" href="/products?category_slug=hoa-sap" title="Hoa Sáp">Hoa Sáp</a>
                        </li>

                        <li class="nav-item ">
                            <a class="nav-link" href="/posts" title="Tin Tức">Tin Tức</a>
                        </li>

                        <li class="nav-item ">
                            <a class="nav-link" href="/contact" title="Liên Hệ">Liên Hệ</a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-nav-overflow"></div>
</header>