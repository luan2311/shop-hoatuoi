@extends('layouts.app')

@section('title', 'Liên Hệ')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contact_style.css') }}">
@endpush

@section('content')
    <div class="layout-contact" style="margin-top:30px">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-12">
                    <div class="contact">
                        <h4>
                            Blossom Corner - Góc Hoa Xinh
                        </h4>
                        <div class="des_foo">
                        </div>
                        <div class="time_work">
                            <div class="item">
                                <b>Địa chỉ:</b>
                                140 Lê Trọng Tấn, Quận Tân Phú
                            </div>
                            <div class="item">
                                <b>Hotline:</b> <a class="fone" href="tel:0898449950" title="0898449950">0898449950</a>
                            </div>
                            <div class="item">
                                <b>Email:</b> <a href="mailto:2001230486@huit.edu.vn" title="2001230486@huit.edu.vn">2001230486@huit.edu.vn</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-contact">
                        <h4>
                            Liên hệ với chúng tôi
                        </h4>
                        <div id="pagelogin">
                            <form method="post" action="/contact" id="contact" accept-charset="UTF-8">
                                @csrf

                                <div class="group_contact">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                            <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-lg @error('name') input-error @enderror" placeholder="Họ và tên">
                                            @error('name')
                                                <span class="text-danger contact-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                            <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg @error('email') input-error @enderror" placeholder="Email">
                                            @error('email')
                                                <span class="text-danger contact-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                                            <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control form-control-lg @error('phone') input-error @enderror" placeholder="Điện thoại" inputmode="numeric">
                                            @error('phone')
                                                <span class="text-danger contact-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                                            <textarea name="message" class="form-control content-area form-control-lg @error('message') input-error @enderror" placeholder="Nội dung" rows="5">{{ old('message') }}</textarea>
                                            @error('message')
                                                <span class="text-danger contact-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                                            <button type="submit" class="btn-lienhe">
                                                Gửi tin nhắn
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-12">
                    <!-- Google Maps Embed API: vị trí cửa hàng -->
                    <div id="contact_map" class="map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.0572728099337!2d106.62625947451755!3d10.806925558631473!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752be27d8b4f4d%3A0x92dcba2950430867!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBUaMawxqFuZyBUUC4gSOG7kyBDaMOtIE1pbmggKEhVSVQp!5e0!3m2!1svi!2s!4v1763790283671!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
