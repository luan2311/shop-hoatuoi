<div class="col-lg-3 col-md-3 col-sm-6 col-6">
    <div class="item_product_main">
        <form action="/cart/add" method="post"
              class="variants product-action item-product-main duration-300" data-cart-form="">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">

            <div class="product-thumbnail">
                <a class="image_thumb scale_hover"
                   href="/products/{{ $product->slug }}"
                   title="{{ $product->name }}">
                    <img class="lazyload duration-300 loaded"
                         src="{{ $product->image_url ?? asset('storage/products/' . $product->image) }}"
                         alt="{{ $product->name }}"
                         data-was-processed="true">
                    @if ($product->stock == 0)
                        <div style="position: absolute; top: 10px; right: 10px; background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                            Ngưng bán
                        </div>
                    @endif
                </a>
                @php
                    $liked = in_array($product->code, \App\Models\Wishlist::productCodesFor(auth()->user()));
                @endphp
                <div class="action-button">
                    {{-- url() tuyệt đối để main.js không bật loading overlay khi bấm tim --}}
                    <a href="{{ url('/wishlist') }}" class="setWishlist btn-views btn-circle {{ $liked ? 'active' : '' }}"
                       data-product-code="{{ $product->code }}"
                       tabindex="0" title="{{ $liked ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' }}">
                        <img width="25" height="25" src="{{ asset($liked ? 'images/heart-red.svg' : 'images/heart.png') }}"
                             class="{{ $liked ? 'img-unlike' : '' }}"
                             alt="Thêm vào yêu thích">
                    </a>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name line-clamp line-clamp-2">
                    <a href="/products/{{ $product->slug }}"
                       title="{{ $product->name }}">
                        {{ $product->name }}
                    </a>
                </h3>
                <div class="product-price-cart">
                    <div class="price-box">
                        <span class="price">{{ $product->formatted_price }}</span>
                    </div>
                    <div class="product-button">
                        @if ($product->stock == 0)
                            <button type="button" class="btn-cart btn-views add_to_cart" disabled
                                    title="Sản phẩm đã ngừng kinh doanh"
                                    style="opacity: 0.5; cursor: not-allowed;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                     fill="currentColor" class="bi bi-cart-plus"
                                     viewBox="0 0 16 16">
                                    <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z">
                                    </path>
                                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z">
                                    </path>
                                </svg>
                            </button>
                        @else
                            <button class="btn-cart btn-views add_to_cart "
                                    title="Thêm vào giỏ hàng">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                     fill="currentColor" class="bi bi-cart-plus"
                                     viewBox="0 0 16 16">
                                    <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z">
                                    </path>
                                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z">
                                    </path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
