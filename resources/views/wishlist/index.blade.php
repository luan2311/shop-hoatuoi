@extends('layouts.app')

@section('title', 'Yêu thích')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/wishlist_style.css') }}">
@endpush

@section('content')
    @php
        $wishlists = $wishlists->filter(fn ($item) => $item->product);
    @endphp

    <div id="wishlist-list" class="container py-5 {{ $wishlists->isEmpty() ? 'd-none' : '' }}">
        <h3 class="text-center mb-4">
            <i class="fas fa-heart text-danger"></i> Sản phẩm yêu thích
        </h3>

        <div class="text-center mb-3">
            <p class="text-muted">Bạn có <strong class="js-wishlist-total">{{ $wishlists->count() }}</strong> sản phẩm yêu thích</p>
        </div>

        <div class="row">
            @foreach ($wishlists as $item)
                @php
                    $product = $item->product;
                    $isDiscontinued = ! $product->is_selling || $product->stock < 1 || $product->status !== 'Còn hàng';
                @endphp

                <div class="col-md-3 mb-4 col-6 js-wishlist-item">
                    <div class="card favorite-card" style="border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.2s ease-in-out; {{ $isDiscontinued ? 'opacity: 0.7;' : '' }}">
                        <div style="position: relative;">
                            <a href="/products/{{ $product->slug }}" class="d-block" style="aspect-ratio: 1/1; overflow: hidden; background: #f8faf8; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                <img src="{{ $product->image_url ?? asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}"
                                     onerror="this.onerror=null;this.src='{{ asset('images/cate_1.png') }}';"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </a>
                            @if ($isDiscontinued)
                                <div style="position: absolute; top: 10px; right: 10px; background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                                    {{ $product->sale_label }}
                                </div>
                            @endif
                        </div>
                        <div class="favorite-info" style="padding: 15px; text-align: center;">
                            <a href="/products/{{ $product->slug }}" title="{{ $product->name }}"
                               style="text-decoration: none; color: #333;">
                                <h5 style="font-size: 17px; font-weight: 500; color: #333; min-height: 50px;">
                                    {{ $product->name }}
                                </h5>
                            </a>
                            <div class="favorite-price" style="color: #ff7a00; font-weight: 600; font-size: 18px; margin: 10px 0;">
                                {{ number_format($product->price, 0, ',', '.') }}₫
                            </div>

                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-1 mt-auto pt-2">
                                @if ($isDiscontinued)
                                    <button type="button"
                                            class="btn-action add-cart"
                                            disabled
                                            title="{{ $product->sale_label }}"
                                            style="background: #e0e0e0; color: #999; border-radius: 8px; padding: 8px 12px; margin: 4px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; cursor: not-allowed; border: none;">
                                        <i class="fas fa-shopping-cart" style="margin-right: 6px;"></i> Thêm giỏ
                                    </button>
                                @else
                                    <form action="/cart/add" method="post" style="margin: 0;" data-no-loading="true" data-cart-form="">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="remove_from_wishlist" value="1">
                                        <input type="hidden" name="product_code" value="{{ $product->code }}">
                                        <button type="submit"
                                                class="btn-action add-cart"
                                                style="background: #e7f4ec; color: #1b5e20; border-radius: 6px; padding: 6px 10px; margin: 2px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; border: 1px solid #c8e6c9;">
                                            <i class="fas fa-shopping-cart me-1"></i> <span class="d-none d-sm-inline">Thêm giỏ</span>
                                        </button>
                                    </form>
                                @endif
                                <button type="button"
                                        class="btn-action remove-fav"
                                        data-product-code="{{ $product->code }}"
                                        style="background: #fbeaea; color: #c62828; border-radius: 6px; padding: 6px 10px; margin: 2px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; border: 1px solid #ffcdd2;">
                                    <i class="fas fa-trash me-1"></i> <span class="d-none d-sm-inline">Xóa</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Yêu thích trống (empty_wishlist.cshtml cũ); JS hiện khối này khi xóa hết sản phẩm --}}
    <div id="wishlist-empty" class="{{ $wishlists->isEmpty() ? '' : 'd-none' }}">
        <div class="CartHeaderContainer">
            <div class="cart--empty-message">
                <i class="fa-regular fa-heart fa-2xl" style="color: #245c36; font-size: 80px; margin: 15px;"></i>
                <p style="font-size:2rem;">Không có sản phẩm nào trong yêu thích của bạn</p>
                <a href="/products" class="btn-continue-shopping">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
@endsection
