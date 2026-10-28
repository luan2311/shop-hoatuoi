@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn - ' . config('app.name', 'Shop Hoa Tươi'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cart_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb_style.css') }}">
    <style>
        #confirmRemoveItemModal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Modal Xác nhận xóa - Phong cách tinh gọn & canh giữa (Editorial Minimalist) */
        #confirmRemoveItemModal .modal-confirm-remove-content {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.18), 0 10px 20px -5px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        #confirmRemoveItemModal .confirm-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #confirmRemoveItemModal .confirm-icon-badge {
            display: inline-flex;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background-color: #fef2f2;
            color: #dc2626;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 0 0 10px #fff5f5;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        #confirmRemoveItemModal:hover .confirm-icon-badge {
            transform: scale(1.05);
        }

        #confirmRemoveItemModal .btn-confirm-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            min-width: 120px;
            height: 42px;
            padding: 0 20px;
            transition: all 0.2s ease;
        }

        #confirmRemoveItemModal .btn-confirm-cancel:hover {
            background-color: #e2e8f0;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        #confirmRemoveItemModal .btn-confirm-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #dc2626;
            color: #ffffff;
            border: 1px solid #dc2626;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            min-width: 140px;
            height: 42px;
            padding: 0 22px;
            box-shadow: 0 3px 8px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
        }

        #confirmRemoveItemModal .btn-confirm-delete:hover {
            background-color: #b91c1c;
            border-color: #b91c1c;
            color: #ffffff;
            box-shadow: 0 5px 12px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }

        #confirmRemoveItemModal .btn-close-subtle {
            width: 32px;
            height: 32px;
            padding: 0;
            background-size: 10px;
            border-radius: 50%;
            opacity: 0.5;
            transition: all 0.2s ease;
        }

        #confirmRemoveItemModal .btn-close-subtle:hover {
            opacity: 1;
            background-color: #f1f5f9;
            transform: rotate(90deg);
        }

        .main-cart-page {
            padding: 20px 0 60px;
            background: #fbfbfb;
            min-height: 60vh;
        }
        .header-cart .title-block-page {
            margin-bottom: 25px;
        }
        .header-cart .title_cart {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
            margin: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #537453;
            display: inline-block;
        }
        .cart-page .cart-header-info {
            background-color: #f8f9fa;
            border: 1px solid #ebebeb;
            border-bottom: none;
            padding: 12px 15px;
            font-weight: 600;
            font-size: 14px;
            color: #444;
            border-radius: 8px 8px 0 0;
        }
        .cart-page .cart_body {
            border: 1px solid #ebebeb;
            border-radius: 0 0 8px 8px;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
        }
        .cart-page .cart_body .ajaxcart__row {
            padding: 15px;
            border-top: 1px solid #ebebeb;
            transition: background-color 0.2s;
        }
        .cart-page .cart_body .ajaxcart__row:hover {
            background-color: #fafbfc;
        }
        .cart-page .cart_body .cart_image {
            width: 100px;
            height: 100px;
            border: 1px solid #eee;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            flex-shrink: 0;
        }
        .cart-page .cart_body .cart_image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .cart-page .cart_body .cart_info .cart_name a {
            color: #333;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
            line-height: 1.4;
        }
        .cart-page .cart_body .cart_info .cart_name a:hover {
            color: #537453;
        }
        .cart-page .cart_body .cart_info .cart_name .remove-item-cart {
            color: #dc3545;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            padding: 0;
        }
        .cart-page .cart_body .cart_info .cart_name .remove-item-cart:hover {
            color: #a71d2a;
            text-decoration: underline;
        }

        /* Định dạng hiển thị cụm chọn số lượng & các nút tăng giảm */
        .cart-page .cart_body .grid .cart_select,
        .cart-mobile .cart_body .cart_select {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
        }

        .cart-page .cart_body .ajaxcart__qty,
        .cart-mobile .cart_body .ajaxcart__qty,
        .form-update-qty {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            height: 32px !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            background: #fff !important;
            overflow: hidden !important;
            position: static !important;
            white-space: nowrap !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .cart-page .cart_body .cart_select input.cart-quantity-input,
        .cart-mobile .cart_body .cart_select input.cart-quantity-input {
            display: inline-block !important;
            width: 44px !important;
            height: 30px !important;
            line-height: 30px !important;
            text-align: center !important;
            border: none !important;
            border-left: 1px solid #ddd !important;
            border-right: 1px solid #ddd !important;
            padding: 0 4px !important;
            margin: 0 !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #222 !important;
            background: #fff !important;
            border-radius: 0 !important;
            outline: none !important;
            box-shadow: none !important;
            float: none !important;
            visibility: visible !important;
            opacity: 1 !important;
            -moz-appearance: textfield !important;
        }

        .cart-page .cart_body .cart_select input.cart-quantity-input::-webkit-outer-spin-button,
        .cart-page .cart_body .cart_select input.cart-quantity-input::-webkit-inner-spin-button,
        .cart-mobile .cart_body .cart_select input.cart-quantity-input::-webkit-outer-spin-button,
        .cart-mobile .cart_body .cart_select input.cart-quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }

        .cart-page .cart_body .cart_select button.btn-minus,
        .cart-page .cart_body .cart_select button.btn-plus,
        .cart-mobile .cart_body .cart_select button.btn-minus,
        .cart-mobile .cart_body .cart_select button.btn-plus {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 30px !important;
            height: 30px !important;
            min-width: 30px !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            background-color: #f8f9fa !important;
            color: #333 !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            outline: none !important;
            box-shadow: none !important;
            float: none !important;
            border-radius: 0 !important;
            visibility: visible !important;
            opacity: 1 !important;
            transition: background-color 0.2s, color 0.2s !important;
        }

        .cart-page .cart_body .cart_select button.btn-minus:hover,
        .cart-page .cart_body .cart_select button.btn-plus:hover,
        .cart-mobile .cart_body .cart_select button.btn-minus:hover,
        .cart-mobile .cart_body .cart_select button.btn-plus:hover {
            background-color: #e2e6ea !important;
            color: #000 !important;
        }

        .cart-page .cart_body .grid .cart_prices .cart-price {
            font-weight: 700;
            font-size: 15px;
            color: #ea8725;
        }
        .cart-page .ajaxcart__footer {
            margin-top: 25px;
            padding: 24px;
            background: #fff;
            border: 1px solid #ebebeb;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
        }
        .cart-page .ajaxcart__footer .btn-proceed-checkout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 42px;
            padding: 0 20px;
            background: #fff;
            color: #537453;
            border: 1px solid #537453;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.2s;
        }
        .cart-page .ajaxcart__footer .btn-proceed-checkout:hover {
            background: #537453;
            color: #fff;
        }
        .cart-page .ajaxcart__footer .btn-clear-cart {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 42px;
            padding: 0 18px;
            background: #fff;
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            cursor: pointer;
        }
        .cart-page .ajaxcart__footer .btn-clear-cart:hover {
            background: #dc3545;
            color: #fff;
        }
        .cart-page .ajaxcart__footer .cart__subtotal {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-transform: uppercase;
        }
        .cart-page .ajaxcart__footer .cart__subtotal .total-price {
            color: #ea8725;
            font-weight: 700;
            font-size: 20px;
        }
        .cart-page .ajaxcart__footer .cart__btn-proceed-checkout-dt a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 46px;
            background: #537453;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
            box-shadow: 0 3px 6px rgba(83, 116, 83, 0.25);
            border: 1px solid #537453;
        }
        .cart-page .ajaxcart__footer .cart__btn-proceed-checkout-dt a:hover {
            background: #3e583e;
            border-color: #3e583e;
            color: #fff;
        }
        /* Mobile styles */
        .cart-mobile .cart_body {
            background: #fff;
            border: 1px solid #ebebeb;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
        }
        .cart-mobile .cart_product {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .cart-mobile .cart_product:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        .cart-mobile .cart_image {
            width: 80px;
            height: 80px;
            border: 1px solid #eee;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }
        .cart-mobile .cart_image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .cart-mobile .ajaxcart__footer {
            margin-top: 20px;
            background: #fff;
            border: 1px solid #ebebeb;
            border-radius: 8px;
            padding: 20px;
        }
        .cart-mobile .ajaxcart__footer .btn-proceed-checkout {
            display: block;
            width: 100%;
            text-align: center;
            height: 42px;
            line-height: 40px;
            border: 1px solid #537453;
            color: #537453;
            background: #fff;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            text-decoration: none;
        }
        .cart-mobile .ajaxcart__footer .btn-proceed-checkout:hover {
            background: #537453;
            color: #fff;
        }
        .cart-mobile .ajaxcart__footer .cart__btn-proceed-checkout-dt a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 46px;
            background: #537453;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .CartHeaderContainer {
            background: #fff;
            border-radius: 12px;
            padding: 50px 20px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #eee;
            margin: 20px auto;
            max-width: 600px;
        }
    </style>
@endpush

@section('content')
<section class="main-cart-page main-container col1-layout">
    <div class="main container cartpcstyle">
        <div class="wrap_background_aside margin-bottom-40">
            @if(empty($lines))
                <!-- Giao diện giỏ hàng trống ported từ empty_cart.cshtml -->
                <div class="CartHeaderContainer">
                    <div class="cart--empty-message">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 201.387 201.387" style="enable-background:new 0 0 201.387 201.387; width:160px; height:160px; fill:#9ca3af; margin: 0 auto 20px; display:block;" xml:space="preserve"> 
                            <g><path d="M129.413,24.885C127.389,10.699,115.041,0,100.692,0C91.464,0,82.7,4.453,77.251,11.916 c-1.113,1.522-0.78,3.657,0.742,4.77c1.517,1.109,3.657,0.78,4.768-0.744c4.171-5.707,10.873-9.115,17.93-9.115 c10.974,0,20.415,8.178,21.963,19.021c0.244,1.703,1.705,2.932,3.376,2.932c0.159,0,0.323-0.012,0.486-0.034 C128.382,28.479,129.679,26.75,129.413,24.885z"></path></g> 
                            <g><path d="M178.712,63.096l-10.24-17.067c-0.616-1.029-1.727-1.657-2.927-1.657h-9.813c-1.884,0-3.413,1.529-3.413,3.413 s1.529,3.413,3.413,3.413h7.881l6.144,10.24H31.626l6.144-10.24h3.615c1.884,0,3.413-1.529,3.413-3.413s-1.529-3.413-3.413-3.413 h-5.547c-1.2,0-2.311,0.628-2.927,1.657l-10.24,17.067c-0.633,1.056-0.648,2.369-0.043,3.439s1.739,1.732,2.97,1.732h150.187 c1.231,0,2.364-0.662,2.97-1.732S179.345,64.15,178.712,63.096z"></path></g> 
                            <g><path d="M161.698,31.623c-0.478-0.771-1.241-1.318-2.123-1.524l-46.531-10.883c-0.881-0.207-1.809-0.053-2.579,0.423 c-0.768,0.478-1.316,1.241-1.522,2.123l-3.509,15c-0.43,1.835,0.71,3.671,2.546,4.099c1.835,0.43,3.673-0.71,4.101-2.546 l2.732-11.675l39.883,9.329l-6.267,26.795c-0.43,1.835,0.71,3.671,2.546,4.099c0.263,0.061,0.524,0.09,0.782,0.09 c1.55,0,2.953-1.062,3.318-2.635l7.045-30.118C162.328,33.319,162.176,32.391,161.698,31.623z"></path></g> 
                            <g><path d="M102.497,39.692l-3.11-26.305c-0.106-0.899-0.565-1.72-1.277-2.28c-0.712-0.56-1.611-0.816-2.514-0.71l-57.09,6.748 c-1.871,0.222-3.209,1.918-2.988,3.791l5.185,43.873c0.206,1.737,1.679,3.014,3.386,3.014c0.133,0,0.27-0.009,0.406-0.024 c1.87-0.222,3.208-1.918,2.988-3.791l-4.785-40.486l50.311-5.946l2.708,22.915c0.222,1.872,1.91,3.202,3.791,2.99 C101.379,43.261,102.717,41.564,102.497,39.692z"></path></g> 
                            <g><path d="M129.492,63.556l-6.775-28.174c-0.212-0.879-0.765-1.64-1.536-2.113c-0.771-0.469-1.696-0.616-2.581-0.406L63.613,46.087 c-1.833,0.44-2.961,2.284-2.521,4.117l3.386,14.082c0.44,1.835,2.284,2.964,4.116,2.521c1.833-0.44,2.961-2.284,2.521-4.117 l-2.589-10.764l48.35-11.626l5.977,24.854c0.375,1.565,1.775,2.615,3.316,2.615c0.265,0,0.533-0.031,0.802-0.096 C128.804,67.232,129.932,65.389,129.492,63.556z"></path></g> 
                            <g><path d="M179.197,64.679c-0.094-1.814-1.592-3.238-3.41-3.238H25.6c-1.818,0-3.316,1.423-3.41,3.238l-6.827,133.12 c-0.048,0.934,0.29,1.848,0.934,2.526c0.645,0.677,1.539,1.062,2.475,1.062h163.84c0.935,0,1.83-0.384,2.478-1.062 c0.643-0.678,0.981-1.591,0.934-2.526L179.197,64.679z M22.364,194.56l6.477-126.293h143.701l6.477,126.293H22.364z"></path></g> 
                            <g><path d="M126.292,75.093c-5.647,0-10.24,4.593-10.24,10.24c0,5.647,4.593,10.24,10.24,10.24c5.647,0,10.24-4.593,10.24-10.24 C136.532,79.686,131.939,75.093,126.292,75.093z M126.292,88.747c-1.883,0-3.413-1.531-3.413-3.413s1.531-3.413,3.413-3.413 c1.882,0,3.413,1.531,3.413,3.413S128.174,88.747,126.292,88.747z"></path></g> 
                            <g><path d="M75.092,75.093c-5.647,0-10.24,4.593-10.24,10.24c0,5.647,4.593,10.24,10.24,10.24c5.647,0,10.24-4.593,10.24-10.24 C85.332,79.686,80.739,75.093,75.092,75.093z M75.092,88.747c-1.882,0-3.413-1.531-3.413-3.413s1.531-3.413,3.413-3.413 s3.413,1.531,3.413,3.413S76.974,88.747,75.092,88.747z"></path></g> 
                            <g><path d="M126.292,85.333h-0.263c-1.884,0-3.413,1.529-3.413,3.413c0,0.466,0.092,0.911,0.263,1.316v17.457 c0,12.233-9.953,22.187-22.187,22.187s-22.187-9.953-22.187-22.187V88.747c0-1.884-1.529-3.413-3.413-3.413 s-3.413,1.529-3.413,3.413v18.773c0,15.998,13.015,29.013,29.013,29.013s29.013-13.015,29.013-29.013V88.747 C129.705,86.863,128.176,85.333,126.292,85.333z"></path></g>
                        </svg>
                        <p style="font-size:2rem; font-weight:600; color:#333; margin-bottom:8px;">Không có sản phẩm nào trong giỏ hàng của bạn</p>
                        <p style="font-size:1.4rem; color:#777; margin-bottom:24px;">Giỏ hàng đang trống.</p>
                        <a class="btn-proceed-checkout btn-checkouts" title="Tiếp tục mua hàng" href="{{ route('products.index') }}" style="background: #537453; color: #fff; padding: 12px 30px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-block;">
                            TIẾP TỤC MUA HÀNG
                        </a>
                    </div>
                </div>
            @else
                <div class="header-cart">
                    <div class="title-block-page">
                        <h1 class="title_cart">
                            <span>Giỏ hàng của bạn</span>
                        </h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="clearfix"></div>

                        <!-- Giao diện Desktop ported từ Cart.cshtml -->
                        <div class="cart-page d-xl-block d-none">
                            <div class="drawer__inner">
                                <div class="CartPageContainer">
                                    <div class="cart-header-info">
                                        <div>Thông tin sản phẩm</div>
                                        <div>Đơn giá</div>
                                        <div>Số lượng</div>
                                        <div>Thành tiền</div>
                                    </div>
                                    <div class="ajaxcart__inner ajaxcart__inner--has-fixed-footer cart_body items">
                                        @foreach($lines as $line)
                                            @php
                                                $prod = $line['product'] ?? null;
                                                $productName = $prod?->name ?? ($line['code'] . ' (không còn tồn tại)');
                                                $productUrl = $prod ? route('products.show', $prod->slug ?? $prod->code) : '#';
                                                $imgUrl = $prod ? \App\Models\Product::resolveImageUrl($prod->image) : null;
                                                if (!$imgUrl) {
                                                    $imgUrl = asset('images/cate_1.png');
                                                }
                                                $formattedPrice = $line['unit_price'] !== null ? number_format($line['unit_price'], 0, ',', '.') . ' đ' : '—';
                                                $formattedSubtotal = number_format($line['subtotal'], 0, ',', '.') . ' đ';
                                            @endphp
                                            <div class="ajaxcart__row" data-product-id="{{ $line['code'] }}">
                                                <div class="ajaxcart__product cart_product">
                                                    <a href="{{ $productUrl }}" class="ajaxcart__product-image cart_image" title="{{ $productName }}">
                                                        <img src="{{ $imgUrl }}" alt="{{ $productName }}" onerror="this.onerror=null;this.src='{{ asset('images/cate_1.png') }}';">
                                                    </a>
                                                    <div class="grid__item cart_info">
                                                        <div class="ajaxcart__product-name-wrapper cart_name">
                                                            <a href="{{ $productUrl }}" class="ajaxcart__product-name h4" title="{{ $productName }}">
                                                                {{ $productName }}
                                                            </a>
                                                            <form method="POST" action="{{ route('cart.remove', $line['code']) }}" class="d-inline form-remove-item" data-no-loading="true">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" title="Xóa" class="cart__btn-remove remove-item-cart ajaxifyCart--remove">
                                                                    <i class="fa-solid fa-trash" aria-label="Remove Item"></i> Xóa
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <div class="grid">
                                                            <div class="grid__item text-right cart_prices">
                                                                <span class="cart-price">{{ $formattedPrice }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="grid">
                                                            <div class="grid__item cart_select">
                                                                <div class="ajaxcart__qty">
                                                                    <form method="POST" action="{{ route('cart.update', $line['code']) }}" class="form-update-qty d-inline-flex align-items-center m-0" data-no-loading="true">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="button" class="btn-qty-minus btn-minus" aria-label="Giảm số lượng">-</button>
                                                                        <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="999" class="cart-quantity-input" data-product-id="{{ $line['code'] }}">
                                                                        <button type="button" class="btn-qty-plus btn-plus" aria-label="Tăng số lượng">+</button>
                                                                        <button type="submit" class="btn-update-submit d-none">Cập nhật</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="grid justify-right">
                                                            <div class="grid__item text-right cart_prices">
                                                                <span class="cart-price item-total-price">{{ $formattedSubtotal }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="ajaxcart__footer ajaxcart__footer--fixed cart-footer">
                                        <div class="row align-items-center">
                                            <div class="col-lg-7 col-12 d-flex flex-wrap gap-2 align-items-center mb-3 mb-lg-0">
                                                <a class="btn-proceed-checkout btn-checkouts" title="Tiếp tục mua hàng" href="{{ route('products.index') }}">
                                                    <i class="fa fa-arrow-left me-2"></i> Tiếp tục mua hàng
                                                </a>
                                                <form method="POST" action="{{ route('cart.clear') }}" class="d-inline m-0 form-clear-cart" data-no-loading="true">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn-clear-cart" title="Làm trống giỏ hàng">
                                                        <i class="fa-solid fa-trash-can me-2"></i> Làm trống giỏ hàng
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="col-lg-5 col-12">
                                                <div class="ajaxcart__subtotal">
                                                    <div class="cart__subtotal">
                                                        <div class="cart__col-6">Tổng tiền:</div>
                                                        <div class="text-right cart__totle">
                                                            <span class="total-price cart-subtotal-price">{{ number_format($total, 0, ',', '.') }} đ</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="cart__btn-proceed-checkout-dt">
                                                    <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg btn-block">
                                                        TIẾN HÀNH THANH TOÁN <i class="fa fa-arrow-right ms-2"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Giao diện Mobile ported từ CartMobileContainer -->
                        <div class="cart-mobile-page d-block d-xl-none">
                            <div class="CartMobileContainer">
                                <div class="cart-mobile">
                                    <div class="cart_body">
                                        @foreach($lines as $line)
                                            @php
                                                $prod = $line['product'] ?? null;
                                                $productName = $prod?->name ?? ($line['code'] . ' (không còn tồn tại)');
                                                $productUrl = $prod ? route('products.show', $prod->slug ?? $prod->code) : '#';
                                                $imgUrl = $prod ? \App\Models\Product::resolveImageUrl($prod->image) : null;
                                                if (!$imgUrl) {
                                                    $imgUrl = asset('images/cate_1.png');
                                                }
                                                $formattedPrice = $line['unit_price'] !== null ? number_format($line['unit_price'], 0, ',', '.') . ' đ' : '—';
                                                $formattedSubtotal = number_format($line['subtotal'], 0, ',', '.') . ' đ';
                                            @endphp
                                            <div class="cart_product" data-product-id="{{ $line['code'] }}">
                                                <div class="cart_image">
                                                    <a href="{{ $productUrl }}" title="{{ $productName }}">
                                                        <img src="{{ $imgUrl }}" alt="{{ $productName }}" onerror="this.onerror=null;this.src='{{ asset('images/cate_1.png') }}';">
                                                    </a>
                                                </div>
                                                <div class="cart_info">
                                                    <div class="cart_name">
                                                        <a href="{{ $productUrl }}" title="{{ $productName }}">
                                                            {{ $productName }}
                                                        </a>
                                                    </div>
                                                    <div class="grid align-items-center justify-content-between mb-2">
                                                        <div class="cart_item_name">
                                                            <span class="cart_quantity">Đơn giá:</span>
                                                            <span class="cart-price">{{ $formattedPrice }}</span>
                                                        </div>
                                                        <div class="cart_prices text-end">
                                                            <form method="POST" action="{{ route('cart.remove', $line['code']) }}" class="d-inline form-remove-item" data-no-loading="true">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="cart__btn-remove remove-item-cart ajaxifyCart--remove border-0 bg-transparent text-danger p-0" title="Xóa">
                                                                    <i class="fa-solid fa-trash"></i> Xóa
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <div class="grid align-items-center justify-content-between">
                                                        <div class="cart_select">
                                                            <div class="ajaxcart__qty">
                                                                <form method="POST" action="{{ route('cart.update', $line['code']) }}" class="form-update-qty d-inline-flex align-items-center m-0" data-no-loading="true">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="button" class="btn-qty-minus btn-minus" aria-label="Giảm số lượng">-</button>
                                                                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="999" class="cart-quantity-input" data-product-id="{{ $line['code'] }}">
                                                                    <button type="button" class="btn-qty-plus btn-plus" aria-label="Tăng số lượng">+</button>
                                                                    <button type="submit" class="btn-update-submit d-none">Cập nhật</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="cart-price item-total-price fw-bold" style="color: #ea8725; font-size: 15px;">{{ $formattedSubtotal }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="ajaxcart__footer">
                                        <div class="cart__subtotal">
                                            <div class="cart__col-6">Tổng tiền:</div>
                                            <div class="cart__totle text-end">
                                                <span class="total-price cart-subtotal-price" style="color: #ea8725; font-size: 18px; font-weight: 700;">{{ number_format($total, 0, ',', '.') }} đ</span>
                                            </div>
                                        </div>
                                        <div class="cart__btn-proceed-checkout-dt">
                                            <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg btn-block mb-2">
                                                TIẾN HÀNH THANH TOÁN <i class="fa fa-arrow-right ms-2"></i>
                                            </a>
                                            <a href="{{ route('products.index') }}" class="btn-proceed-checkout btn-checkouts mb-2">
                                                Tiếp tục mua hàng
                                            </a>
                                            <form method="POST" action="{{ route('cart.clear') }}" class="text-center mt-2 form-clear-cart" data-no-loading="true">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-link text-danger text-decoration-none btn-sm btn-clear-cart" title="Làm trống giỏ hàng">
                                                    <i class="fa-solid fa-trash-can me-1"></i> Làm trống giỏ hàng
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('modals')
<!-- Modal Popup Xác nhận xóa sản phẩm -->
<div class="modal fade" id="confirmRemoveItemModal" tabindex="-1" aria-labelledby="confirmRemoveItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content modal-confirm-remove-content position-relative border-0">
            <!-- Nút đóng góc phải tinh tế -->
            <button type="button" class="btn-close btn-close-subtle position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>

            <!-- Toàn bộ nội dung canh giữa, không chia cắt header/body/footer riêng biệt và không có nền header -->
            <div class="modal-body text-center p-4 pt-5 pb-4">
                <!-- Icon biểu tượng xóa nổi bật ở giữa -->
                <div class="confirm-icon-wrapper mb-3">
                    <span class="confirm-icon-badge">
                        <i class="fa-solid fa-trash-can"></i>
                    </span>
                </div>

                <!-- Tiêu đề canh giữa, không background -->
                <h4 class="modal-title fw-bold text-center mb-2" id="confirmRemoveItemModalLabel" style="font-size: 20px; color: #1e293b; letter-spacing: -0.01em;">
                    Xác nhận xóa sản phẩm
                </h4>

                <!-- Nội dung thông báo canh giữa (đã bỏ tên sản phẩm theo yêu cầu) -->
                <p class="confirm-desc text-center mb-4" id="confirmRemoveModalDesc" style="font-size: 15px; color: #64748b; line-height: 1.5; margin: 0 auto 24px; max-width: 300px;">
                    Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?
                </p>

                <!-- Các nút hành động canh giữa -->
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <button type="button" class="btn btn-confirm-cancel" data-bs-dismiss="modal">
                        Hủy bỏ
                    </button>
                    <button type="button" id="btn-confirm-delete-item" class="btn btn-confirm-delete">
                        <i class="fa-solid fa-trash me-1"></i> <span id="btn-confirm-delete-text">Xóa sản phẩm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var actionType = 'item'; // 'item' hoặc 'clear'
        var targetProductToDelete = null;

        // Đảm bảo modal nằm trực tiếp dưới <body> để không bị ảnh hưởng bởi CSS transform / stacking context
        var modalEl = document.getElementById('confirmRemoveItemModal');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Mở popup xác nhận xóa 1 sản phẩm
        function promptRemoveProduct(productId) {
            actionType = 'item';
            targetProductToDelete = productId;

            $('#confirmRemoveItemModalLabel').text('Xác nhận xóa sản phẩm');
            $('#confirmRemoveModalDesc').text('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?');
            $('#btn-confirm-delete-text').text('Xóa sản phẩm');

            showConfirmModal();
        }

        // Mở popup xác nhận làm trống toàn bộ giỏ hàng
        function promptClearCart() {
            actionType = 'clear';
            targetProductToDelete = null;

            $('#confirmRemoveItemModalLabel').text('Làm trống giỏ hàng');
            $('#confirmRemoveModalDesc').text('Bạn có chắc chắn muốn xóa toàn bộ sản phẩm trong giỏ hàng?');
            $('#btn-confirm-delete-text').text('Xóa toàn bộ');

            showConfirmModal();
        }

        function showConfirmModal() {
            var modalEl = document.getElementById('confirmRemoveItemModal');
            if (modalEl && modalEl.parentNode !== document.body) {
                document.body.appendChild(modalEl);
            }

            // Đảm bảo loading overlay không che đè modal
            var loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('hidden');
                loadingOverlay.style.display = 'none';
            }

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else if (typeof $.fn.modal !== 'undefined') {
                $(modalEl).modal('show');
            } else {
                var confirmMsg = actionType === 'clear'
                    ? 'Bạn có chắc chắn muốn xóa toàn bộ sản phẩm trong giỏ hàng?'
                    : 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?';
                if (confirm(confirmMsg)) {
                    if (actionType === 'clear') {
                        executeClearCart();
                    } else {
                        executeRemoveItem(targetProductToDelete);
                    }
                }
            }
        }

        // Đóng modal thủ công khi click data-bs-dismiss="modal"
        $(document).on('click', '#confirmRemoveItemModal [data-bs-dismiss="modal"]', function (e) {
            e.preventDefault();
            var modalEl = document.getElementById('confirmRemoveItemModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            } else if (typeof $.fn.modal !== 'undefined') {
                $(modalEl).modal('hide');
            }
        });

        // Đồng bộ số lượng giỏ hàng trên header (chỉ cập nhật icon giỏ hàng, không đụng tới wishlist)
        function syncCartHeaderCount(count) {
            if (typeof window.updateCartCount === 'function') {
                window.updateCartCount(count);
            } else {
                $('.js-cart-count, .header-action_cart .count_item_pr').text(count);
            }
        }

        // Thực thi làm trống toàn bộ giỏ hàng qua AJAX
        function executeClearCart() {
            var modalEl = document.getElementById('confirmRemoveItemModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            } else if (typeof $.fn.modal !== 'undefined') {
                $(modalEl).modal('hide');
            }

            var form = $('.form-clear-cart:first');
            var url = form.attr('action') || '/cart/clear';

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: csrfToken
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function (res) {
                    syncCartHeaderCount(0);
                    window.location.reload();
                },
                error: function () {
                    if (form.length) {
                        form[0].submit();
                    } else {
                        window.location.reload();
                    }
                }
            });
        }

        // Thực thi xóa sản phẩm qua AJAX
        function executeRemoveItem(productId) {
            var modalEl = document.getElementById('confirmRemoveItemModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            } else if (typeof $.fn.modal !== 'undefined') {
                $(modalEl).modal('hide');
            }

            var form = $('[data-product-id="' + productId + '"]').find('.form-remove-item');
            var url = '/cart/items/' + encodeURIComponent(productId);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: csrfToken
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function (res) {
                    if (res.isEmpty) {
                        window.location.reload();
                    } else {
                        $('[data-product-id="' + productId + '"]').fadeOut(250, function () {
                            $(this).remove();
                        });
                        $('.cart-subtotal-price').text(res.cartSubtotal);
                        syncCartHeaderCount(res.cartCount);
                    }
                },
                error: function () {
                    if (form.length) {
                        form[0].submit();
                    } else {
                        window.location.reload();
                    }
                }
            });
        }

        // Bấm nút xóa trong popup modal
        $('#btn-confirm-delete-item').on('click', function (e) {
            e.preventDefault();
            if (actionType === 'clear') {
                executeClearCart();
            } else if (targetProductToDelete) {
                executeRemoveItem(targetProductToDelete);
            }
        });

        // Hàm cập nhật giỏ hàng qua AJAX (kế thừa logic từ Cart.cshtml)
        function updateCart(productId, quantity, $sourceInput) {
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
            }
            if (quantity > 999) {
                quantity = 999;
            }

            // Đồng bộ giá trị input trên cả desktop và mobile
            $('.cart-quantity-input[data-product-id="' + productId + '"]').val(quantity);

            $.ajax({
                url: '/cart/items/' + encodeURIComponent(productId),
                type: 'POST',
                data: {
                    _method: 'PATCH',
                    _token: csrfToken,
                    quantity: quantity
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        var rows = $('[data-product-id="' + productId + '"]');
                        rows.find('.item-total-price').text(response.itemTotalPrice);
                        $('.cart-subtotal-price').text(response.cartSubtotal);
                        syncCartHeaderCount(response.cartCount);
                    } else if (response.message) {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function (xhr) {
                    if ($sourceInput && $sourceInput.closest('form').length) {
                        $sourceInput.closest('form')[0].submit();
                    }
                }
            });
        }

        // Debounce khi gõ số lượng trực tiếp
        var debounceTimer = null;
        $(document).on('input', '.cart-quantity-input', function () {
            var input = $(this);
            var productId = input.data('product-id');
            var quantity = parseInt(input.val(), 10);

            if (quantity <= 0 || isNaN(quantity)) {
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                updateCart(productId, quantity, input);
            }, 400);
        });

        // Khi người dùng nhập số lượng <= 0 rồi rời khỏi input (blur/change) -> hỏi xóa
        $(document).on('change', '.cart-quantity-input', function (e) {
            var input = $(this);
            var quantity = parseInt(input.val(), 10);
            if (quantity <= 0 || isNaN(quantity)) {
                input.val(1);
                promptRemoveProduct(input.data('product-id'));
            }
        });

        // Nút giảm số lượng (-)
        $(document).on('click', '.btn-qty-minus', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var input = $(this).siblings('.cart-quantity-input');
            if (!input.length) input = $(this).closest('.form-update-qty, .cart_select').find('.cart-quantity-input');
            var currentVal = parseInt(input.val(), 10) || 1;
            var productId = input.data('product-id');

            if (currentVal > 1) {
                var newVal = currentVal - 1;
                input.val(newVal);
                updateCart(productId, newVal, input);
            } else {
                // Số lượng hiện tại là 1 mà bấm trừ -> hiển thị popup hỏi xóa
                promptRemoveProduct(productId);
            }
        });

        // Nút tăng số lượng (+)
        $(document).on('click', '.btn-qty-plus', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var input = $(this).siblings('.cart-quantity-input');
            if (!input.length) input = $(this).closest('.form-update-qty, .cart_select').find('.cart-quantity-input');
            var currentVal = parseInt(input.val(), 10) || 1;
            if (currentVal < 999) {
                var newVal = currentVal + 1;
                input.val(newVal);
                updateCart(input.data('product-id'), newVal, input);
            }
        });

        // Bấm chữ "Xóa" hoặc icon thùng rác -> hiển thị popup hỏi xóa
        $(document).on('click', '.remove-item-cart', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var row = $(this).closest('[data-product-id]');
            var productId = row.data('product-id');
            promptRemoveProduct(productId);
        });

        // Bắt sự kiện submit form xóa (nếu có nhấn Enter)
        $(document).on('submit', '.form-remove-item', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var row = $(this).closest('[data-product-id]');
            var productId = row.data('product-id');
            promptRemoveProduct(productId);
        });

        // Bấm nút làm trống giỏ hàng (Desktop & Mobile)
        $(document).on('click', '.btn-clear-cart', function (e) {
            e.preventDefault();
            e.stopPropagation();
            promptClearCart();
        });

        // Bắt sự kiện submit form làm trống giỏ hàng
        $(document).on('submit', '.form-clear-cart', function (e) {
            e.preventDefault();
            e.stopPropagation();
            promptClearCart();
        });
    });
</script>
@endpush
