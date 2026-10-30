@php
    $mobileCartCount = 0;
    if (auth()->check()) {
        try {
            $uCart = app(\App\Services\SalesService::class)->cart(auth()->user());
            if ($uCart && is_array($uCart->items)) {
                $mobileCartCount = array_sum(array_column($uCart->items, 'quantity'));
            }
        } catch (\Throwable $e) {
            try {
                $uid = auth()->id();
                $ids = [(string) $uid];
                if (\MongoDB\BSON\ObjectId::isValid($uid)) {
                    $ids[] = new \MongoDB\BSON\ObjectId($uid);
                }
                $uCart = \App\Models\Cart::whereIn('user_id', $ids)->first();
                if ($uCart && is_array($uCart->items)) {
                    $mobileCartCount = array_sum(array_column($uCart->items, 'quantity'));
                }
            } catch (\Throwable $e2) {}
        }
    }
    $mobileWishlistCount = 0;
    try {
        $mobileWishlistCount = count(\App\Models\Wishlist::productCodesFor(auth()->user()));
    } catch (\Throwable $e) {}
@endphp

<!-- Bottom Navigation Bar cố định cho Mobile & Tablet (< 992px) -->
<nav class="mobile-bottom-nav d-lg-none" aria-label="Thanh điều hướng nhanh trên thiết bị di động">
    <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->is('/') ? 'active' : '' }}" title="Trang chủ">
        <div class="bottom-nav-icon">
            <i class="fa-solid fa-house"></i>
        </div>
        <span class="bottom-nav-label">Trang chủ</span>
    </a>

    <a href="{{ route('products.index') }}" class="bottom-nav-item {{ request()->is('products*') || request()->is('tat-ca-san-pham') || request()->is('bo-hoa-tuoi') || request()->is('ke-hoa-chuc-mung') || request()->is('gio-hoa-chuc-mung') || request()->is('hoa-cuoi') || request()->is('hoa-sap') ? 'active' : '' }}" title="Danh mục sản phẩm">
        <div class="bottom-nav-icon">
            <i class="fa-solid fa-shapes"></i>
        </div>
        <span class="bottom-nav-label">Sản phẩm</span>
    </a>

    <a href="{{ route('wishlist.index') }}" class="bottom-nav-item {{ request()->is('wishlist*') ? 'active' : '' }}" title="Danh sách yêu thích">
        <div class="bottom-nav-icon position-relative">
            <i class="fa-solid fa-heart"></i>
            <span class="bottom-nav-badge js-wishlist-count {{ $mobileWishlistCount > 0 ? '' : 'd-none' }}">{{ $mobileWishlistCount }}</span>
        </div>
        <span class="bottom-nav-label">Yêu thích</span>
    </a>

    <a href="{{ route('cart.index') }}" class="bottom-nav-item {{ request()->is('cart*') ? 'active' : '' }}" title="Giỏ hàng">
        <div class="bottom-nav-icon position-relative">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="bottom-nav-badge js-cart-count {{ $mobileCartCount > 0 ? '' : 'd-none' }}">{{ $mobileCartCount }}</span>
        </div>
        <span class="bottom-nav-label">Giỏ hàng</span>
    </a>

    @auth
        <a href="{{ route('orders.index') }}" class="bottom-nav-item {{ request()->is('account*') || request()->is('profile*') ? 'active' : '' }}" title="Tài khoản & Đơn hàng">
            <div class="bottom-nav-icon">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <span class="bottom-nav-label">Tài khoản</span>
        </a>
    @else
        <a href="{{ route('login') }}" class="bottom-nav-item {{ request()->is('login*') || request()->is('register*') ? 'active' : '' }}" title="Đăng nhập tài khoản">
            <div class="bottom-nav-icon">
                <i class="fa-regular fa-user"></i>
            </div>
            <span class="bottom-nav-label">Tài khoản</span>
        </a>
    @endauth
</nav>
