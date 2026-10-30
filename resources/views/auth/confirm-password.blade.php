@extends('layouts.app')

@section('title', 'Xác nhận mật khẩu - Góc Hoa Xinh')

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
                                    <span>Xác nhận mật khẩu</span>
                                </h1>

                                <p class="text-muted text-center small mb-4">
                                    Đây là khu vực bảo mật. Vui lòng xác nhận lại mật khẩu của bạn trước khi tiếp tục.
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

                                <form method="POST" action="{{ route('password.confirm') }}" class="form-signup">
                                    @csrf

                                    <div class="form-group mb-4">
                                        <label for="password" class="form-label fw-semibold text-secondary small">Mật khẩu của bạn <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password"
                                                   id="password"
                                                   name="password"
                                                   class="form-control form-control-lg border-start-0 ps-0 @error('password') is-invalid @enderror"
                                                   placeholder="Nhập mật khẩu"
                                                   required
                                                   autocomplete="current-password">
                                        </div>
                                    </div>

                                    <div class="pull-xs-left mb-3">
                                        <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                            XÁC NHẬN
                                        </button>
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
