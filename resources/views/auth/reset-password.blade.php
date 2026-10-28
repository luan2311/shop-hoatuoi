@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu - Góc Hoa Xinh')

@section('content')
<div class="bg-gray py-4">
    <section class="section">
        <div class="container">
            <div class="page_login">
                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-7 col-sm-10 col-12 account-animate">
                        <div class="page-login pagecustome clearfix">
                            <div class="wpx">
                                <h1 class="title_heads a-center">
                                    <span>Đặt lại mật khẩu</span>
                                </h1>

                                <p class="text-muted text-center small mb-4">
                                    Nhập mật khẩu mới cho tài khoản của bạn.
                                </p>

                                @if ($errors->any())
                                    <div class="alert alert-danger mb-4" style="font-size: 14px;">
                                        <ul class="mb-0 ps-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.store') }}" class="form-signup">
                                    @csrf

                                    <!-- Password Reset Token -->
                                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                    <!-- Email Address -->
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label fw-semibold text-secondary small">Email tài khoản <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email"
                                                   id="email"
                                                   name="email"
                                                   class="form-control form-control-lg border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                   value="{{ old('email', $request->email) }}"
                                                   placeholder="Email tài khoản"
                                                   required
                                                   autofocus
                                                   autocomplete="username">
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class="form-group mb-3">
                                        <label for="password" class="form-label fw-semibold text-secondary small">Mật khẩu mới <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password"
                                                   id="password"
                                                   name="password"
                                                   class="form-control form-control-lg border-start-0 ps-0 @error('password') is-invalid @enderror"
                                                   placeholder="Nhập mật khẩu mới"
                                                   required
                                                   autocomplete="new-password">
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="form-group mb-4">
                                        <label for="password_confirmation" class="form-label fw-semibold text-secondary small">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="fas fa-shield-alt"></i>
                                            </span>
                                            <input type="password"
                                                   id="password_confirmation"
                                                   name="password_confirmation"
                                                   class="form-control form-control-lg border-start-0 ps-0"
                                                   placeholder="Xác nhận mật khẩu mới"
                                                   required
                                                   autocomplete="new-password">
                                        </div>
                                    </div>

                                    <div class="pull-xs-left mb-3">
                                        <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                            CẬP NHẬT MẬT KHẨU
                                        </button>
                                    </div>

                                    <div class="btn_boz_khac text-center">
                                        <a href="{{ route('login') }}" class="btn-link-style w-100">
                                            &larr; Quay lại đăng nhập
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
