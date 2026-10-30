@extends('layouts.app')

@section('title', 'Đăng nhập tài khoản')

@section('content')
@php
    $returnUrl = request('returnUrl', request('ReturnUrl', ''));
@endphp

<div class="bg-gray">
    <section class="section">
        <div class="container">
            <div class="page_login">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xl-4 offset-0 offset-xl-4 offset-lg-4 offset-md-3 col-12 account-animate">
                        <div class="page-login pagecustome clearfix">
                            <div class="wpx">
                                <!-- Tiêu đề Đăng nhập chính -->
                                <h1 class="title_heads a-center" id="mainLoginTitle"><span>Đăng nhập</span></h1>

                                @if (session('status'))
                                    <div class="alert alert-success">{{ session('status') }}</div>
                                @endif
                                @if (session('ForgotResult'))
                                    <div class="alert alert-success">{{ session('ForgotResult') }}</div>
                                @endif
                                @if (session('ResetSuccess'))
                                    <div class="alert alert-success">{{ session('ResetSuccess') }}</div>
                                @endif
                                @if (session('ForgotError'))
                                    <div class="alert alert-danger">{{ session('ForgotError') }}</div>
                                @endif

                                <!-- Box Đăng nhập -->
                                <div id="login" class="section">
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <input type="hidden" name="returnUrl" value="{{ $returnUrl }}" />

                                        @if ($errors->any())
                                            <div class="alert alert-danger" style="display: block; margin-bottom: 15px; font-size: 14px;">
                                                @foreach ($errors->all() as $error)
                                                    <p style="margin: 0;">{{ $error }}</p>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="form-signup clearfix">
                                            <fieldset class="form-group">
                                                <label for="TenDangNhap" class="form-label">
                                                    Tên đăng nhập <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                       class="form-control form-control-lg"
                                                       value="{{ old('email', old('TenDangNhap')) }}"
                                                       name="TenDangNhap"
                                                       id="TenDangNhap"
                                                       placeholder="Nhập tên đăng nhập hoặc email"
                                                       required
                                                       autofocus />
                                            </fieldset>

                                            <fieldset class="form-group">
                                                <label for="MatKhau" class="form-label">
                                                    Mật khẩu <span class="text-danger">*</span>
                                                </label>
                                                <input type="password"
                                                       class="form-control form-control-lg"
                                                       name="MatKhau"
                                                       id="MatKhau"
                                                       placeholder="Nhập mật khẩu"
                                                       required />
                                            </fieldset>

                                            <div class="action-btn-submit">
                                                <input class="btn btn-primary btn-style" type="submit" value="ĐĂNG NHẬP" />
                                                <button type="submit" style="position: absolute; top: 0; left: -10px; width: 1px; height: 1px; opacity: 0; border: none; padding: 0; margin: 0; z-index: 10;" tabindex="-1"></button>
                                            </div>

                                            <div class="btn_boz_khac">
                                                <div class="btn_khac">
                                                    <span class="quenmk" style="cursor: pointer;">Quên mật khẩu?</span>
                                                    <a href="{{ route('register') }}" class="btn-link-style btn-register" title="Đăng ký tại đây">Đăng ký tại đây</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Box Quên mật khẩu (.h_recover) -->
                                <div class="h_recover" style="display:none;">
                                    <h1 class="title_heads a-center"><span>Lấy lại mật khẩu</span></h1>
                                    <div id="recover-password" class="form-signup page-login">
                                        <form method="POST" action="{{ route('password.email') }}">
                                            @csrf
                                            <div class="form-signup clearfix">
                                                <p class="text-muted small text-center mb-3">Chúng tôi sẽ gửi cho bạn một email để kích hoạt việc đặt lại mật khẩu.</p>
                                                <fieldset class="form-group">
                                                    <label for="recover-email" class="form-label">
                                                        Email <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="email"
                                                           class="form-control form-control-lg"
                                                           value="{{ old('email') }}"
                                                           name="email"
                                                           id="recover-email"
                                                           placeholder="Nhập địa chỉ email của bạn"
                                                           required />
                                                </fieldset>
                                            </div>
                                            <div class="action_bottom">
                                                <input class="btn btn-primary btn-style" style="margin-top: 0px;" type="submit" value="Gửi">
                                                <span class="btn btn-link quay_lai" style="cursor: pointer; font-size: 15px; font-weight: bold; color: #537453;">Hủy</span>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var loginForm = document.getElementById('login');
        var recoverDiv = document.querySelector('.h_recover');
        var mainTitle = document.getElementById('mainLoginTitle');

        function showRecoverForm(e) {
            if (e) e.preventDefault();
            if (loginForm) {
                loginForm.style.display = 'none';
                loginForm.classList.add('hidden', 'd-none');
            }
            if (mainTitle) {
                mainTitle.style.display = 'none';
            }
            if (recoverDiv) {
                recoverDiv.style.display = 'block';
                recoverDiv.classList.remove('d-none');
            }
        }

        function showLoginForm(e) {
            if (e) e.preventDefault();
            if (recoverDiv) {
                recoverDiv.style.display = 'none';
                recoverDiv.classList.add('d-none');
            }
            if (loginForm) {
                loginForm.style.display = 'block';
                loginForm.classList.remove('hidden', 'd-none');
            }
            if (mainTitle) {
                mainTitle.style.display = 'block';
            }
        }

        var quenmkBtn = document.querySelector('.quenmk');
        var quaylaiBtn = document.querySelector('.quay_lai');
        if (quenmkBtn) quenmkBtn.addEventListener('click', showRecoverForm);
        if (quaylaiBtn) quaylaiBtn.addEventListener('click', showLoginForm);

        // Kiểm tra URL nếu có ?show_recover
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('show_recover')) {
            showRecoverForm();
        }
    });
</script>
@endpush
