@extends('layouts.app')

@section('title', $product->name . ' - Shop Hoa Tươi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb_style.css') }}">
    <style>
        .product-image-main {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            background: #fff;
        }
        .product-image-main img {
            width: 100%;
            height: auto;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-image-main:hover img {
            transform: scale(1.03);
        }
        .details-pro .title-product {
            font-size: 1.8rem;
            color: #2b2b2b;
            font-weight: 700;
            margin-bottom: 12px;
        }
        /* Custom styles chuẩn hóa theo codebase C# ASP MVC 5 Shop-Flower */
        .details-product .details-pro .form-inline .boz-form .flex-quantity .custom-btn-number .input_number_product {
            display: inline-flex !important;
            align-items: stretch;
            vertical-align: middle;
        }
        .details-product .details-pro .form-inline .boz-form .flex-quantity .custom-btn-number .input_number_product input#qtym {
            width: 50px !important;
            height: 40px !important;
            line-height: 40px !important;
            border-top: 1px solid #ddd !important;
            border-bottom: 1px solid #ddd !important;
            border-left: none !important;
            border-right: none !important;
            box-shadow: none !important;
            outline: none !important;
            font-size: 1.4rem;
            text-align: center;
            background: #fff;
            padding: 0 !important;
            margin: 0 !important;
        }
        .details-product .details-pro .form-inline .boz-form .flex-quantity .custom-btn-number .input_number_product .btn_num {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            height: 40px !important;
            width: 35px !important;
            min-width: 35px !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 1.6rem;
            font-weight: 600;
            line-height: 1;
            border: 1px solid #ddd !important;
            background: #f8f8f8;
            color: #333;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
            text-decoration: none;
        }
        .details-product .details-pro .form-inline .boz-form .flex-quantity .custom-btn-number .input_number_product .btn_num:hover {
            background: #537453 !important;
            color: #fff !important;
            border-color: #537453 !important;
        }
        .details-product .details-pro .form-inline .boz-form .flex-quantity .button_actions .btn.btn_add_cart svg {
            color: #ea8725;
            transition: color 0.3s;
        }
        .details-product .details-pro .form-inline .boz-form .flex-quantity .button_actions .btn.btn_add_cart:hover svg {
            color: #fff;
            fill: #fff;
        }
        .details-product .product-col-right .block-policy .policy-content .col-12:last-child .policy-item {
            border-bottom: none;
        }
        .product-review-details {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 24px;
            margin-top: 30px;
        }
        .product-review-details .title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #537453;
            border-bottom: 2px solid #537453;
            display: inline-block;
            padding-bottom: 6px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
<div class="py-2 mb-4 breadcrumb_background bg-light">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="py-2 mb-0 breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted">Sản phẩm</a></li>
                @if($category = data_get($product->category, 'name'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('products.index', ['category_slug' => data_get($product->category, 'slug')]) }}" class="text-decoration-none text-muted">
                            {{ $category }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="pt-0 pb-5 product layout-product" itemscope itemtype="https://schema.org/Product">
    <div class="container">
        <div class="details-product">
            <div class="row margin-bottom-30">
                <!-- Cột trái: Hình ảnh sản phẩm (col-lg-4 theo chuẩn C# chi_tiet_san_pham.cshtml) -->
                <div class="mb-4 col-lg-4 col-md-6 col-12 product-detail-left product-images">
                    <div class="relative product-image-block">
                        <div class="product-image-main">
                            @php
                                $imgSrc = $product->image_url ?? asset('storage/products/' . $product->image);
                            @endphp
                            @if($product->image)
                                <img src="{{ $imgSrc }}"
                                     alt="{{ $product->name }}"
                                     class="mx-auto rounded img-fluid d-block">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height: 380px;">
                                    Chưa có ảnh
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Cột giữa: Thông tin chi tiết sản phẩm (col-lg-5 theo chuẩn C# chi_tiet_san_pham.cshtml) -->
                <div class="mb-4 col-lg-5 col-md-6 col-12 details-pro">
                    <h1 class="title-product">{{ $product->name }}</h1>

                    <div class="clearfix mb-2 product-top">
                        <div class="clearfix sku-product small text-muted">
                            <span class="variant-sku">Mã: <span class="a-sku fw-bold">{{ $product->code }}</span></span>
                            @if($categoryName = data_get($product->category, 'name'))
                                <span class="mx-2">|</span>
                                <span>Danh mục: <strong class="text-dark">{{ $categoryName }}</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 inventory_quantity">
                        <span class="mb-break">
                            <span class="stock-brand-title">Thương hiệu:</span>
                            <span class="a-vendor fw-bold">
                                {{ $product->brand ?? 'Góc Hoa Xinh' }}
                            </span>
                        </span>
                        <span class="line">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                        <span class="mb-break">
                            <span class="stock-brand-title">Tình trạng:</span>
                            <span class="a-stock fw-bold {{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $product->sale_label }}
                            </span>
                        </span>
                    </div>

                    <!-- Form Thêm vào giỏ hàng chuẩn C# chi_tiet_san_pham.cshtml -->
                    <form action="/cart/add" method="post" id="add-to-cart-form" class="form-inline" data-cart-form="">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="clearfix price-box">
                            <div class="special-price">
                                <span class="price product-price">{{ $product->formatted_price }}</span>
                                @if($product->original_price && $product->original_price > $product->price)
                                    <span class="text-muted text-decoration-line-through ms-2 fs-6">
                                        {{ number_format($product->original_price, 0, ',', '.') }} đ
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-product">
                            <div class="clearfix box-variant">
                                <input type="hidden" id="one_variant" name="variantId" value="171650208">
                            </div>

                            <div class="boz-form">
                                <div class="flex-quantity">
                                    <div class="custom custom-btn-number show">
                                        <span>Số lượng: </span>
                                        <div class="input_number_product">
                                            <button class="btn_num num_1 button button_qty" onclick="var result = document.getElementById('qtym'); var qtypro = parseInt(result.value); if( !isNaN( qtypro ) && qtypro > 1 ) result.value = qtypro - 1; return false;" type="button">-</button>
                                            <input type="text" id="qtym" name="quantity"
                                                   value="1" maxlength="3" class="form-control prd_quantity"
                                                   onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false;"
                                                   onchange="var v = parseInt(this.value); if(isNaN(v) || v < 1) this.value = 1; else this.value = v;">
                                            <button class="btn_num num_2 button button_qty" onclick="var result = document.getElementById('qtym'); var qtypro = parseInt(result.value); if( !isNaN( qtypro )) result.value = qtypro + 1; return false;" type="button"><span>+</span></button>
                                        </div>
                                        @if($product->stock < 5 && $product->stock > 0)
                                            <div class="text-danger small mt-1 text-center font-weight-normal ms-2" style="font-size: 11px;">(Còn {{ $product->stock }} sp)</div>
                                        @endif
                                    </div>
                                    <div class="clearfix btn-mua button_actions">
                                        @if(! $product->is_selling || $product->stock < 1 || $product->status !== 'Còn hàng')
                                            <button type="button" class="btn btn_base normal_button btn_add_cart add_to_cart btn-cart" disabled="disabled" style="width: 100%;">
                                                <span class="txt-main">{{ $product->sale_label }}</span>
                                            </button>
                                        @else
                                            <button type="submit" name="buy_now" value="1" class="btn btn-buyNow">
                                                <span class="txt-main">Mua ngay</span>
                                                <span>Giao tận nơi hoặc nhận tại cửa hàng</span>
                                            </button>
                                            <button type="submit" class="btn btn_base normal_button btn_add_cart add_to_cart btn-cart">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16"> <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"></path> <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"></path></svg>
                                                <span class="txt-main">Thêm vào giỏ</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Cột phải: Chính sách hỗ trợ ported từ C# chi_tiet_san_pham.cshtml -->
                <div class="mb-4 col-lg-3 col-md-12 col-12 product-col-right">
                    <div class="block-policy">
                        <div class="heading-policy">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"></path>
                            </svg>
                            Chính sách hỗ trợ
                        </div>
                        <div class="row policy-content">
                            <div class="col-lg-12 col-md-6 col-sm-6 col-12">
                                <div class="policy-item duration-300">
                                    <div class="icon aspect-1">
                                        <img width="50" height="50" class="lazyload loaded" data-src="{{ asset('images/product_policy_1.svg') }}" alt="Góc Hoa Xinh" src="{{ asset('images/product_policy_1.svg') }}" data-was-processed="true">
                                    </div>
                                    <div class="info">
                                        <h3>Duyệt ảnh sản phẩm</h3>
                                        <span>Gửi hình ảnh sản phẩm ưng ý mới giao hàng</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-6 col-sm-6 col-12">
                                <div class="policy-item duration-300">
                                    <div class="icon aspect-1">
                                        <img width="50" height="50" class="lazyload loaded" data-src="{{ asset('images/product_policy_2.svg') }}" alt="Góc Hoa Xinh" src="{{ asset('images/product_policy_2.svg') }}" data-was-processed="true">
                                    </div>
                                    <div class="info">
                                        <h3>Thẻ chúc mừng miễn phí</h3>
                                        <span>Miễn phí thiệp và nội dụng theo yêu cầu</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-6 col-sm-6 col-12">
                                <div class="policy-item duration-300">
                                    <div class="icon aspect-1">
                                        <img width="50" height="50" class="lazyload loaded" data-src="{{ asset('images/product_policy_3.svg') }}" alt="Góc Hoa Xinh" src="{{ asset('images/product_policy_3.svg') }}" data-was-processed="true">
                                    </div>
                                    <div class="info">
                                        <h3>Banner sản phẩm</h3>
                                        <span>Miễn phí banner sản phẩm</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-6 col-sm-6 col-12">
                                <div class="policy-item duration-300">
                                    <div class="icon aspect-1">
                                        <img width="50" height="50" class="lazyload loaded" data-src="{{ asset('images/product_policy_4.svg') }}" alt="Góc Hoa Xinh" src="{{ asset('images/product_policy_4.svg') }}" data-was-processed="true">
                                    </div>
                                    <div class="info">
                                        <h3>Giao hàng trong 2H</h3>
                                        <span>Giao nhanh nếu sản phẩm cần gấp</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thêm yêu thích chuẩn C# chi_tiet_san_pham.cshtml -->
                    <div class="gr-wish-compare">
                        <div class="product-wish">
                            <a href="javascript:void(0)" class="setWishlist btn-views" data-product-code="{{ $product->code }}" tabindex="0" title="Thêm vào yêu thích">
                                <img width="25" height="25" src="{{ asset('images/heart.png') }}" alt="Thêm vào yêu thích">
                                Thêm vào yêu thích
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Khối mô tả chi tiết sản phẩm (XSS-safe, giữ HTML gốc từ C# database) -->
            <div class="row">
                <div class="col-lg-12 col-12">
                    <div class="product-review-details">
                        <div class="title">Thông tin sản phẩm</div>
                        <div class="product-review-content">
                            {!! strip_tags($product->description ?? '', '<p><br><b><strong><i><em><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6><table><tr><td><th><tbody><thead><img><hr><a>') !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Khối sản phẩm liên quan (Sản phẩm cùng danh mục) -->
            @if(isset($relatedProducts) && $relatedProducts->count() > 0)
                <div class="mt-5 row">
                    <div class="mb-3 col-12">
                        <h2 class="fw-bold fs-4" style="color: #537453; border-left: 4px solid #537453; padding-left: 10px;">
                            Sản phẩm cùng danh mục
                        </h2>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            @foreach($relatedProducts as $item)
                                @include('partials.product-card', ['product' => $item])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($product->stock > 0)
        <!-- Sticky Bottom Purchase Bar cho Mobile (< 992px) -->
        <div class="mobile-sticky-product-bar d-flex d-lg-none">
            <div class="sticky-price-wrap">
                <span class="sticky-price">{{ $product->formatted_price }}</span>
                <span class="sticky-sku">{{ $product->name }}</span>
            </div>
            <div class="sticky-btn-group">
                <form action="/cart/add" method="post" class="d-inline m-0" data-cart-form="" data-no-loading="true">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn-sticky-cart" title="Thêm vào giỏ">
                        <i class="fa-solid fa-cart-plus me-1"></i> Giỏ
                    </button>
                </form>
                <form action="/cart/add" method="post" class="d-inline m-0">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="buy_now" value="1">
                    <button type="submit" class="btn-sticky-buy" title="Mua ngay">
                        Mua ngay
                    </button>
                </form>
            </div>
        </div>
    @endif
</section>
@endsection
