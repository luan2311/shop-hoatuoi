@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/trangchu_style.css') }}">
@endpush

@section('content')
    <!-- Hero carousel -->
    <div id="heroCarousel" class="section_slider hero-carousel carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Banner 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Banner 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Banner 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/89k.png') }}" alt="Banner quảng cáo 89k" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <div class="hero-slide">
                    <img src="{{ asset('images/slide-giao-hoa-2h.jpg') }}" alt="Giao hoa nhanh trong 2 giờ">
                    <div class="hero-text">
                        <p class="hero-title">GIAO HOA<br>NHANH 2H</p>
                        <p class="hero-sub"><span class="hero-line"></span>nội thành TP.HCM</p>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="hero-slide">
                    <img src="{{ asset('images/slide-hoa-tuoi-moi.jpg') }}" alt="100% hoa tươi mới mỗi ngày">
                    <div class="hero-text">
                        <p class="hero-title">100% HOA<br>TƯƠI MỚI</p>
                        <p class="hero-sub"><span class="hero-line"></span>nhập mới mỗi ngày</p>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Trước</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sau</span>
        </button>
    </div>
    <div class="section_services">
        <div class="container">
            <div class="row promo-box">
                <div class="col-lg-3 col-md-3 col-sm-6 col-6">
                    <div class="promo-item duration-300">
                        <div class="icon aspect-1">
                            <img width="50" height="50" class="lazyload loaded" alt="Góc Hoa Xinh"
                                 src="{{ asset('images/ser_1.svg') }}" data-was-processed="true">
                        </div>
                        <div class="info">
                            <h3>
                                Giao hoa 2h
                            </h3>
                            <span>
                                Giao nhanh nội thành, cam kết đúng giờ
                            </span>

                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-6 col-6">
                    <div class="promo-item duration-300">
                        <div class="icon aspect-1">
                            <img width="50" height="50" class="lazyload loaded" alt="Góc Hoa Xinh"
                                 src="{{ asset('images/ser_2.svg') }}" data-was-processed="true">
                        </div>
                        <div class="info">
                            <h3>
                                Xuất hóa đơn VAT
                            </h3>
                            <span>
                                Xuất VAT trong ngày (8%)
                            </span>

                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-6 col-6">
                    <div class="promo-item duration-300">
                        <div class="icon aspect-1">
                            <img width="50" height="50" class="lazyload loaded" alt="Góc Hoa Xinh"
                                 src="{{ asset('images/ser_3.svg') }}" data-was-processed="true">
                        </div>
                        <div class="info">
                            <h3>
                                100% tươi mới
                            </h3>
                            <span>
                                Hoa tươi mới mỗi ngày không héo úa
                            </span>

                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-6 col-6">
                    <div class="promo-item duration-300">
                        <div class="icon aspect-1">
                            <img width="50" height="50" class="lazyload loaded" alt="Góc Hoa Xinh"
                                 src="{{ asset('images/ser_4.svg') }}" data-was-processed="true">
                        </div>
                        <div class="info">
                            <h3>
                                Hotline: <a href="tel:0898449950" title="Hotline">0898449950</a>
                            </h3>
                            <span>
                                Tư vấn theo phù hợp giá tiền
                            </span>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Danh mục nổi bật -->
    <section class="section-index section_category">
        <div class="container">

            <div class="section-title">
                <h2>
                    Danh mục nổi bật
                </h2>
            </div>
            <div class="swiper_category swiper-container swiper-container-initialized swiper-container-horizontal swiper-container-pointer-events">
                <div class="swiper-wrapper" style="transform: translate3d(0px, 0px, 0px); align-items: center; justify-content: center;">

                    <div class="swiper-slide swiper-slide-active" style="width: 172.667px; margin-right: 40px;">
                        <a href="/products?category_slug=bo-hoa-tuoi" title="Bó hoa tươi" class="cate-item">
                            <div class="box-cate">
                                <div class="cate-image aspect-1">
                                    <img alt="Slider 5" width="250" height="250" class="lazyload duration-300 loaded"
                                         src="{{ asset('images/cate_1.png') }}" data-was-processed="true">
                                </div>
                                <h3 class="cate-name line-clamp line-clamp-1 duration-300">
                                    Bó hoa tươi
                                </h3>
                            </div>
                        </a>
                    </div>

                    <div class="swiper-slide swiper-slide-next" style="width: 172.667px; margin-right: 40px;">
                        <a href="/products?category_slug=ke-chuc-mung" title="Kệ khai trương" class="cate-item">
                            <div class="box-cate">
                                <div class="cate-image aspect-1">
                                    <img alt="Slider 5" width="250" height="250" class="lazyload duration-300 loaded"
                                         src="{{ asset('images/cate_2.png') }}" data-was-processed="true">
                                </div>
                                <h3 class="cate-name line-clamp line-clamp-1 duration-300">
                                    Kệ khai trương
                                </h3>
                            </div>
                        </a>
                    </div>

                    <div class="swiper-slide" style="width: 172.667px; margin-right: 40px;">
                        <a href="/products?category_slug=gio-hoa-chuc-mung" title="Hoa chúc mừng" class="cate-item">
                            <div class="box-cate">
                                <div class="cate-image aspect-1">
                                    <img alt="Slider 5" width="250" height="250" class="lazyload duration-300 loaded"
                                         src="{{ asset('images/cate_3.png') }}" data-was-processed="true">
                                </div>
                                <h3 class="cate-name line-clamp line-clamp-1 duration-300">
                                    Hoa chúc mừng
                                </h3>
                            </div>
                        </a>
                    </div>

                    <div class="swiper-slide" style="width: 172.667px; margin-right: 40px;">
                        <a href="/products?category_slug=hoa-sap" title="Hoa sáp" class="cate-item">
                            <div class="box-cate">
                                <div class="cate-image aspect-1">
                                    <img alt="Slider 5" width="250" height="250" class="lazyload duration-300 loaded"
                                         src="{{ asset('images/cate_4.png') }}" data-was-processed="true">
                                </div>
                                <h3 class="cate-name line-clamp line-clamp-1 duration-300">
                                    Hoa sáp
                                </h3>
                            </div>
                        </a>
                    </div>

                    <div class="swiper-slide" style="width: 172.667px; margin-right: 40px;">
                        <a href="/products?category_slug=hoa-cuoi" title="Hoa Cưới" class="cate-item">
                            <div class="box-cate">
                                <div class="cate-image aspect-1">
                                    <img alt="Slider 5" width="250" height="250" class="lazyload duration-300 loaded"
                                         src="{{ asset('images/cate_5.png') }}" data-was-processed="true">
                                </div>
                                <h3 class="cate-name line-clamp line-clamp-1 duration-300">
                                    Hoa Cưới
                                </h3>
                            </div>
                        </a>
                    </div>

                </div>
                <div class="swiper-pagination swiper-pagination-clickable swiper-pagination-lock swiper-pagination-bullets">
                    <span class="swiper-pagination-bullet swiper-pagination-bullet-active"></span>
                </div>
            </div>

        </div>
    </section>

    <!-- Sản phẩm bán chạy -->
    <section class="section-index section_product_tab section_product_tab_best">
        <div class="container">
            <div class="wrap_tab_index not-dqtab e-tabs ajax-tab-1">
                <div class="section-title">
                    <h2>
                        <a href="/products">
                            Sản phẩm bán chạy
                        </a>
                    </h2>
                </div>
                <div class="block-products">
                    <div class="tab-content current">
                        <div class="row load-after">
                            @foreach ($banChay as $product)
                                @include('partials.product-card')
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bó hoa tươi  -->
    <section class="section-index section_product_tab section_product_tab_1">
        <div class="container">
            <div class="wrap_tab_index not-dqtab e-tabs ajax-tab-1" data-section-2="ajax-tab-1">
                <div class="section-title">
                    <h2>
                        <a href="/products?category_slug=bo-hoa-tuoi">
                            Bó hoa tươi
                        </a>
                    </h2>

                    <div class="tab_big">
                        <div class="tab_ul">
                            <ul class="tabs tabs-title tab-pc tabtitle2 ajax clearfix">
                                <li class="tab-link tab_cate has-content current" data-tab="tab1-1">
                                    <span>Bó hoa tươi</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="block-products">
                    <div class="tab1-1 tab-content current">
                        <div class="row load-after" data-section="section_product_tab_1">
                            @foreach ($boHoaTuoi as $product)
                                @include('partials.product-card')
                            @endforeach
                        </div>
                        <div class="view-more clearfix">
                            <a href="/products?category_slug=bo-hoa-tuoi" title="Xem tất cả" class="btn" style="font-size:1.4rem">
                                Xem tất cả
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--  Kệ hoa chúc mừng -->
    <section class="section-index section_product_tab section_product_tab_2">
        <div class="container">
            <div class="wrap_tab_index not-dqtab e-tabs ajax-tab-1" data-section-2="ajax-tab-1">
                <div class="section-title">
                    <h2>
                        <a href="/products?category_slug=ke-chuc-mung">
                            Kệ mừng khai trương
                        </a>
                    </h2>
                    <div class="tab_big">
                        <div class="tab_ul">
                            <ul class="tabs tabs-title tab-pc tabtitle2 ajax clearfix">
                                <li class="tab-link tab_cate has-content current" data-tab="tab2-1">
                                    <span>Kệ Hoa Khai Trương</span>
                                </li>

                                <li class="tab-link tab_cate " data-tab="tab2-2">
                                    <span>Giỏ hoa mừng</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="block-products">
                    <div class="tab2-1 tab-content current">
                        <div class="row load-after" data-section="section_product_tab_2">
                            @foreach ($keHoa as $product)
                                @include('partials.product-card')
                            @endforeach
                        </div>
                        <div class="view-more clearfix">
                            <a href="/products?category_slug=ke-chuc-mung" title="Xem tất cả" class="btn" style="font-size:1.4rem">
                                Xem tất cả
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="tab2-2 tab-content">
                        <div class="row load-after" data-section="section_product_tab_2">
                            @foreach ($gioHoa as $product)
                                @include('partials.product-card')
                            @endforeach
                        </div>
                        <div class="view-more clearfix">
                            <a href="/products?category_slug=gio-hoa-chuc-mung" title="Xem tất cả" class="btn" style="font-size:1.4rem">
                                Xem tất cả
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment -->
    <div id="js-global-alert" class="alert alert-success" role="alert">
        <button type="button" class="close"><span aria-hidden="true"><span aria-hidden="true">×</span></span></button>
        <h5 class="alert-heading"></h5>
        <p class="alert-content"></p>
    </div>
    <section class="section-index section_about" id="about">
        <div class="container">
            <div class="row">

                <div class="col-lg-12 col-md-12 col-sm-12 about_page">
                    <div class="collection-summary rte">
                        <div>
                            <h1 style="text-align: center;">
                                Góc Hoa Xinh - Nơi Kết Nối Cảm Xúc Qua Những Bó Hoa Tươi Tắn
                            </h1>
                            <p>
                                Bạn đang tìm kiếm một <a href="/">
                                    <strong>
                                        cửa hàng hoa
                                        tươi
                                    </strong>
                                </a> uy tín tại TP.HCM, nơi không chỉ cung cấp những bó hoa đẹp mắt
                                mà còn mang đến trải nghiệm dịch vụ tận tâm? <strong>Shop hoa gần đây</strong> ở Quận 7
                                - là địa điểm lý tưởng dành cho bạn. Với sự đam mê và sáng tạo trong từng sản phẩm,
                                chúng tôi cam kết mang đến những bó hoa ý nghĩa, giúp bạn truyền tải thông điệp yêu
                                thương đến người nhận trong mọi dịp đặc biệt.
                            </p>
                            <h2>Về Cửa Hàng Hoa Tươi Chất Lượng - Sự Kết Hợp Hoàn Hảo Giữa Nghệ Thuật Và Cảm Xúc</h2>
                            <p>
                                Cửa hàng của chúng tôi không chỉ là một <a href="/">
                                    <strong>
                                        tiệm hoa
                                        quận 7
                                    </strong>
                                </a> thông thường, mà còn là nơi kết hợp hài hòa giữa nghệ thuật
                                và tình cảm. Chúng tôi hiểu rằng mỗi bó hoa đều mang theo một thông điệp riêng biệt, đại
                                diện cho tình cảm mà bạn muốn gửi gắm. Từ những <strong>bó hoa yêu thương</strong> đơn
                                giản cho đến những lẵng hoa sang trọng, mỗi sản phẩm đều được thiết kế và chăm chút tỉ
                                mỉ để mang lại sự hài lòng tuyệt đối cho khách hàng.
                            </p>
                            <p>
                                Những <strong>bó hoa yêu thương</strong> đầy sắc màu từ <strong>
                                    cửa hàng hoa
                                    tươi
                                </strong> của chúng tôi tại Quận 7 không chỉ tạo sự tươi mới mà còn thể hiện sự
                                tinh tế trong từng chi tiết. Đây chính là sự lựa chọn hoàn hảo để bạn gửi đi yêu thương
                                trong mọi dịp.
                            </p>
                            <p style="text-align: center;">
                                <img alt="Bó hoa tươi đầy màu sắc từ Góc Hoa Xinh, shop hoa tươi uy tín tại Quận 7, mang đến sự tươi mới và tinh tế cho mọi dịp"
                                     data-thumb="original" original-height="640" original-width="640"
                                     src="{{ asset('images/shop-hoa-tuoi-goc-hoa-xinh-ba8c9601-b5c9-49d4-803d-35a049880323.jpg') }}"
                                     style="width: 640px; height: 640px;">
                            </p>
                            <h2>Sứ Mệnh - Lan Tỏa Yêu Thương Qua Từng Bó Hoa Tươi</h2>
                            <p>
                                Chúng tôi mang trong mình sứ mệnh lan tỏa yêu thương và kết nối mọi người qua từng bông
                                hoa tươi thắm. <a href="/"><strong>Shop hoa gần đây</strong></a> cam
                                kết mang đến cho bạn:
                            </p>
                            <ul>
                                <li>
                                    <strong>Sự tươi mới và chất lượng:</strong> Chúng tôi lựa chọn những bông hoa đẹp
                                    nhất, được nhập khẩu từ các trang trại uy tín trong và ngoài nước, đảm bảo độ tươi
                                    và vẻ đẹp lâu bền.
                                </li>
                                <li>
                                    <strong>Dịch vụ tận tâm:</strong> Từ khâu tư vấn, thiết kế đến giao hàng, chúng tôi
                                    luôn lấy khách hàng làm trung tâm, nhằm mang đến trải nghiệm mua sắm hoàn hảo nhất.
                                </li>
                                <li>
                                    <strong>Sáng tạo trong thiết kế:</strong> Mỗi sản phẩm hoa tại <strong>
                                        cửa hàng hoa
                                        tươi
                                    </strong> của chúng tôi đều mang phong cách riêng biệt, sáng tạo và độc đáo.
                                    Bạn sẽ luôn tìm thấy sự mới mẻ và cá nhân hóa trong từng bó hoa.
                                </li>
                            </ul>
                            <h3>Lợi Ích Khi Chọn Một <strong>Shop Hoa Gần Đây</strong></h3>
                            <p>
                                Việc chọn một <strong>tiệm hoa quận 7</strong> gần bạn sẽ giúp tiết kiệm thời gian và đảm
                                bảo rằng những <strong>bó hoa yêu thương</strong> sẽ được giao đến tay người nhận nhanh
                                chóng, với độ tươi mới hoàn hảo. Chúng tôi cung cấp dịch vụ <strong>
                                    giao hoa tận
                                    nơi
                                </strong> nhanh chóng và tiện lợi, phù hợp cho những dịp quan trọng khi bạn cần
                                hoa gấp.
                            </p>
                            <p>
                                Nếu bạn đang sinh sống tại khu vực Quận 7 hoặc lân cận, <strong>shop hoa gần đây</strong>
                                của chúng tôi cam kết sẽ đáp ứng mọi nhu cầu của bạn một cách chu đáo và kịp thời.
                            </p>
                            <p style="text-align: center;">
                                <img alt="Thiết kế hoa sang trọng từ cửa hàng hoa tươi Góc Hoa Xinh, giao hoa nhanh chóng và chất lượng tại Sài Gòn"
                                     data-thumb="original" original-height="798" original-width="798"
                                     src="{{ asset('images/shop-hoa-tuoi-quan-7-57fc4e53-3f86-47cb-bb5c-ecfb9d8c80d6.jpg') }}"
                                     style="width: 600px; height: 600px;">
                            </p>
                            <h2>Sản Phẩm Đa Dạng Tại <strong>Cửa Hàng Hoa Tươi Quận 7</strong></h2>
                            <p>
                                <strong>Cửa hàng hoa tươi</strong> của chúng tôi không chỉ cung cấp hoa tươi cho những
                                dịp thông thường mà còn có những sản phẩm độc đáo dành cho mọi sự kiện đặc biệt. Bạn có
                                thể dễ dàng tìm thấy hoa chúc mừng sinh nhật, hoa khai trương, hoa tặng người yêu, và
                                các lẵng hoa chia buồn trang trọng.
                            </p>
                            <p>
                                Với mỗi sản phẩm, chúng tôi đều đặt tình yêu nghề vào từng chi tiết, đảm bảo rằng thông
                                điệp bạn muốn gửi gắm sẽ được thể hiện một cách chân thành và ý nghĩa nhất. Những
                                <strong>bó hoa yêu thương</strong> mà chúng tôi tạo ra không chỉ mang đến vẻ đẹp tinh tế
                                mà còn gửi gắm tình cảm trọn vẹn từ người tặng đến người nhận.
                            </p>
                            <h2><strong>Hoa Tươi Sài Gòn</strong> - Chất Lượng Và Độc Đáo Từ Tiệm Hoa Uy Tín</h2>
                            <p>
                                Sài Gòn là một thành phố năng động, với thị trường hoa tươi đa dạng và phong phú.
                                <strong>Shop hoa gần đây</strong> tại Quận 7 tự hào là một trong những đơn vị cung cấp
                                hoa tươi Sài Gòn chất lượng, được tuyển chọn từ các nhà cung cấp uy tín. Điều này giúp
                                chúng tôi luôn đáp ứng được nhu cầu hoa tươi đa dạng của khách hàng, đảm bảo bạn luôn có
                                những lựa chọn hoàn hảo cho mọi dịp.
                            </p>
                            <p>
                                Chúng tôi tự hào là <strong>tiệm hoa quận 7</strong> nổi bật với sự sáng tạo, không ngừng
                                cập nhật những xu hướng hoa mới nhất. Mỗi <strong>bó hoa yêu thương</strong> tại cửa
                                hàng đều là một tác phẩm nghệ thuật, mang phong cách độc đáo và sự tận tâm của những
                                người thợ hoa chuyên nghiệp.
                            </p>
                            <p style="text-align: center;">
                                <img alt="Bó hoa yêu thương từ tiệm hoa gần đây, Góc Hoa Xinh, nơi kết nối yêu thương qua từng cánh hoa."
                                     data-thumb="original" original-height="798" original-width="798"
                                     src="{{ asset('images/shop-hoa-tuoi-quan-7-hcm.jpg') }}" style="width: 640px; height: 640px;">
                            </p>
                            <h2><strong>Gửi Đi Yêu Thương Qua Những Bó Hoa Tươi Thắm</strong></h2>
                            <p>
                                Chúng tôi tin rằng hoa là phương tiện hoàn hảo để truyền tải cảm xúc, và không có gì ý
                                nghĩa hơn khi bạn có thể gửi đi tình yêu thương của mình qua những bó hoa tươi đẹp.
                                Những <strong>bó hoa yêu thương</strong> từ <strong>cửa hàng hoa tươi</strong> của chúng
                                tôi được thiết kế tỉ mỉ, nhằm truyền tải sự ấm áp và tình cảm chân thành từ người tặng
                                đến người nhận.
                            </p>
                            <p>
                                Cho dù là dịp lễ, sinh nhật, hay chỉ đơn giản là muốn gửi đi một thông điệp yêu thương
                                vào một ngày bình thường, <strong>shop hoa gần đây</strong> của chúng tôi luôn sẵn sàng
                                giúp bạn tạo ra những kỷ niệm đáng nhớ với những bó hoa tinh tế và lãng mạn.
                            </p>
                            <h2>Tại Sao Nên Chọn <strong>Tiệm Hoa Quận 7</strong> Cho Mọi Dịp Quan Trọng?</h2>
                            <p>
                                Nếu bạn đang ở Quận 7 và muốn tìm kiếm một <strong>cửa hàng hoa tươi</strong> uy tín,
                                chất lượng, thì <strong>tiệm hoa quận 7</strong> của chúng tôi là sự lựa chọn lý tưởng.
                                Chúng tôi không chỉ cung cấp dịch vụ giao hoa nhanh chóng mà còn mang đến những bó hoa
                                được thiết kế tinh tế, độc đáo, phù hợp với nhu cầu của khách hàng.
                            </p>
                            <p>
                                Với đội ngũ thợ cắm hoa giàu kinh nghiệm, mỗi bó hoa tại <strong>
                                    shop hoa gần
                                    đây
                                </strong> đều được chăm chút kỹ lưỡng, từ việc chọn hoa cho đến cách phối màu và
                                thiết kế tổng thể. Chúng tôi cam kết mang lại sự hài lòng tuyệt đối, giúp bạn dễ dàng
                                gửi đi những thông điệp yêu thương đến người thân yêu.
                            </p>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Hoa sáp -->
    <section class="section-index section_product_tab section_product_tab_3">
        <div class="container">
            <div class="wrap_tab_index not-dqtab e-tabs ajax-tab-1" data-section-2="ajax-tab-1">
                <div class="section-title">
                    <h2>
                        <a href="/products?category_slug=hoa-sap">
                            Hoa Sáp
                        </a>
                    </h2>

                    <div class="tab_big">
                        <div class="tab_ul">
                            <ul class="tabs tabs-title tab-pc tabtitle2 ajax clearfix">
                                <li class="tab-link tab_cate has-content current" data-tab="tab3-1">
                                    <span>Hoa Sáp</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="block-products">
                    <div class="tab3-1 tab-content current">
                        <div class="row load-after" data-section="section_product_tab_3">
                            @foreach ($hoaSap as $product)
                                @include('partials.product-card')
                            @endforeach
                        </div>
                        <div class="view-more clearfix">
                            <a href="/products?category_slug=hoa-sap" title="Xem tất cả" class="btn" style="font-size:1.4rem">
                                Xem tất cả
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hoa Cưới -->
    <section class="section-index section_product_tab section_product_tab_4">
        <div class="container">
            <div class="wrap_tab_index not-dqtab e-tabs ajax-tab-1" data-section-2="ajax-tab-1">
                <div class="section-title">
                    <h2>
                        <a href="/products?category_slug=hoa-cuoi">
                            Hoa Cưới
                        </a>
                    </h2>

                    <div class="tab_big">
                        <div class="tab_ul">
                            <ul class="tabs tabs-title tab-pc tabtitle2 ajax clearfix">
                                <li class="tab-link tab_cate has-content current" data-tab="tab4-1">
                                    <span>Hoa Cầm Tay</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="block-products">
                    <div class="tab4-1 tab-content current">
                        <div class="row load-after" data-section="section_product_tab_4">
                            @foreach ($hoaCuoi as $product)
                                @include('partials.product-card')
                            @endforeach
                        </div>
                        <div class="view-more clearfix">
                            <a href="/products?category_slug=hoa-cuoi" title="Xem tất cả" class="btn" style="font-size:1.4rem">
                                Xem tất cả
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tin tức mới -->
    <section class="section-index section_news">
        <div class="container">
            <div class="section-title">
                <h2>
                    <a href="/posts">
                        Tin tức mới
                    </a>
                </h2>
            </div>
            <div class="row">
                @forelse ($tinTucMoi as $post)
                    <div class="col-lg-4 col-md-4 col-12">
                        <div class="news-item duration-300">
                            <a class="news-thumb" href="/posts/{{ $post->slug }}" title="{{ $post->title }}">
                                <img src="{{ $post->thumbnail ? asset('storage/posts/' . $post->thumbnail) : asset('images/slide-giao-hoa-2h.jpg') }}"
                                     alt="{{ $post->title }}">
                            </a>
                            <div class="news-info">
                                <p class="time-post">{{ optional($post->published_at)->format('d/m/Y') }}</p>
                                <h3 class="line-clamp line-clamp-2">
                                    <a href="/posts/{{ $post->slug }}" title="{{ $post->title }}">{{ $post->title }}</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center news-empty">Chưa có bài viết nào.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Chuyển tab "Kệ Hoa Khai Trương" / "Giỏ hoa mừng"
            const tabLinks = document.querySelectorAll('.section_product_tab_2 .tab-link');

            tabLinks.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const targetTab = this.getAttribute('data-tab');

                    tabLinks.forEach(function (t) {
                        t.classList.remove('current');
                    });
                    this.classList.add('current');

                    document.querySelectorAll('.section_product_tab_2 .tab-content').forEach(function (content) {
                        content.classList.toggle('current', content.classList.contains(targetTab));
                    });
                });
            });
        });
    </script>
@endpush
