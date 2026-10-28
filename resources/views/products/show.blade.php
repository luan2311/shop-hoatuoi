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
        .details-pro .special-price .price {
            font-size: 2rem;
            color: #d63384;
            font-weight: 700;
        }
        .btn-buyNow {
            background-color: #537453;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn-buyNow:hover {
            opacity: 0.9;
            color: #fff;
        }
        .btn_add_cart {
            border: 2px solid #537453;
            color: #537453;
            background: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        .btn_add_cart:hover {
            background: #537453;
            color: #fff;
        }
        .block-policy {
            background: #fdfdfd;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 16px;
        }
        .block-policy .heading-policy {
            font-weight: 700;
            color: #537453;
            font-size: 1.1rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .policy-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .policy-item .info h3 {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
        }
        .policy-item .info span {
            font-size: 0.8rem;
            color: #6c757d;
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
        <nav aria-label="breadcrumb">
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
                <!-- Cột trái: Hình ảnh sản phẩm -->
                <div class="mb-4 col-lg-5 col-md-6 col-12 product-detail-left product-images">
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

                <!-- Cột giữa: Thông tin chi tiết sản phẩm -->
                <div class="mb-4 col-lg-4 col-md-6 col-12 details-pro">
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

                    <div class="mb-3 inventory_quantity small">
                        <span class="mb-break">
                            <span class="stock-brand-title text-muted">Thương hiệu:</span>
                            <span class="a-vendor fw-bold text-dark">{{ $product->brand ?? 'Góc Hoa Xinh' }}</span>
                        </span>
                        <span class="mx-2 text-muted">|</span>
                        <span class="mb-break">
                            <span class="stock-brand-title text-muted">Tình trạng:</span>
                            <span class="a-stock fw-bold {{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $product->status ?? ($product->stock > 0 ? 'Còn hàng' : 'Hết hàng') }}
                            </span>
                            <span class="text-muted ms-1">· Tồn kho: {{ $product->stock }}</span>
                        </span>
                    </div>

                    <!-- Form Thêm vào giỏ hàng -->
                    <form action="/cart/add" method="post" id="add-to-cart-form" class="mt-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="clearfix mb-3 price-box">
                            <div class="special-price">
                                <span class="price product-price text-danger fw-bold fs-3">{{ $product->formatted_price }}</span>
                                @if($product->original_price && $product->original_price > $product->price)
                                    <span class="text-muted text-decoration-line-through ms-2 fs-6">
                                        {{ number_format($product->original_price, 0, ',', '.') }} đ
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-product">
                            <div class="gap-3 mb-3 d-flex align-items-center">
                                <label for="quantity" class="mb-0 form-label fw-semibold text-muted">Số lượng:</label>
                                <div class="input-group" style="width: 120px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="const q=document.getElementById('quantity'); if(parseInt(q.value)>1) q.value=parseInt(q.value)-1;">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}" class="text-center form-control" style="font-weight: 600;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="const q=document.getElementById('quantity'); q.value=parseInt(q.value)+1;">+</button>
                                </div>
                            </div>

                            <div class="flex-wrap gap-2 mt-3 d-flex">
                                @if($product->stock == 0)
                                    <button type="button" class="px-4 py-2 btn btn-secondary" disabled style="cursor: not-allowed;">
                                        Sản phẩm tạm hết hàng
                                    </button>
                                @else
                                    <button type="submit" class="gap-2 px-4 py-2 btn btn-outline-success btn_add_cart d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
                                            <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"></path>
                                            <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"></path>
                                        </svg>
                                        Thêm vào giỏ
                                    </button>
                                    <button type="submit" name="buy_now" value="1" class="px-4 py-2 btn btn-success btn-buyNow">
                                        Mua ngay
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Cột phải: Chính sách hỗ trợ ported từ C# chi_tiet_san_pham.cshtml -->
                <div class="mb-4 col-lg-3 col-md-12 col-12 product-col-right">
                    <div class="block-policy">
                        <div class="heading-policy">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                                <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                            </svg>
                            Chính sách hỗ trợ
                        </div>
                        <div class="policy-content">
                            <div class="policy-item">
                                <div class="icon">
                                    <img width="36" height="36" src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/product_policy_1.svg?1757405855081" alt="Duyệt ảnh">
                                </div>
                                <div class="info">
                                    <h3>Duyệt ảnh sản phẩm</h3>
                                    <span>Gửi ảnh chụp hoa ưng ý trước khi giao</span>
                                </div>
                            </div>
                            <div class="policy-item">
                                <div class="icon">
                                    <img width="36" height="36" src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/product_policy_2.svg?1757405855081" alt="Thiệp chúc mừng">
                                </div>
                                <div class="info">
                                    <h3>Thiệp chúc mừng free</h3>
                                    <span>Tặng thiệp và in nội dung theo yêu cầu</span>
                                </div>
                            </div>
                            <div class="policy-item">
                                <div class="icon">
                                    <img width="36" height="36" src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/product_policy_3.svg?1757405855081" alt="Banner sản phẩm">
                                </div>
                                <div class="info">
                                    <h3>Banner miễn phí</h3>
                                    <span>Thiết kế banner chữ đẹp sang trọng</span>
                                </div>
                            </div>
                            <div class="policy-item">
                                <div class="icon">
                                    <img width="36" height="36" src="//bizweb.dktcdn.net/100/487/411/themes/957692/assets/product_policy_4.svg?1757405855081" alt="Giao nhanh 2h">
                                </div>
                                <div class="info">
                                    <h3>Giao nhanh trong 2H</h3>
                                    <span>Giao hỏa tốc nội thành đúng hẹn</span>
                                </div>
                            </div>
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
</section>
@endsection
