@extends('layouts.app')

@section('title', 'Xác thực Email - Góc Hoa Xinh')

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
                                    <span>Xác thực Email</span>
                                </h1>

                                <p class="text-muted text-center small mb-4">
                                    Cảm ơn bạn đã đăng ký tài khoản tại Góc Hoa Xinh! Trước khi bắt đầu, vui lòng nhấp vào liên kết xác thực chúng tôi vừa gửi đến địa chỉ email của bạn. Nếu chưa nhận được email, chúng tôi sẵn sàng gửi lại cho bạn.
                                </p>

                                @if (session('status') == 'verification-link-sent')
                                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                        <i class="fas fa-check-circle me-1"></i> Một liên kết xác thực mới đã được gửi đến địa chỉ email mà bạn đã cung cấp khi đăng ký.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="d-flex flex-column gap-3">
                                    <form method="POST" action="{{ route('verification.send') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                            GỬI LẠI EMAIL XÁC THỰC
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('logout') }}" class="text-center">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-muted small text-decoration-none">
                                            <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất tài khoản
                                        </button>
                                    </form>
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
