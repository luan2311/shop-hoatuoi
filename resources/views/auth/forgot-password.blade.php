<x-guest-layout>
    <div class="bg-gray py-5">
        <section class="section">
            <div class="container">
                <div class="page_login">
                    <div class="row justify-content-center">
                        <div class="col-lg-5 col-md-7 col-sm-10 col-12 account-animate">
                            <div class="page-login pagecustome clearfix">
                                <div class="wpx">
                                    <h1 class="title_heads a-center">
                                        <span>Quên mật khẩu</span>
                                    </h1>

                                    <p class="text-muted text-center small mb-4">
                                        Vui lòng nhập địa chỉ email bạn đã đăng ký. Hệ thống sẽ gửi cho bạn liên kết khôi phục mật khẩu.
                                    </p>

                                    @if (session('status'))
                                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                            <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger mb-4" style="font-size: 14px;">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('password.email') }}" class="form-signup">
                                        @csrf

                                        <div class="form-group mb-4">
                                            <label for="email" class="form-label fw-semibold text-secondary small">Email xác thực <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email"
                                                       id="email"
                                                       name="email"
                                                       class="form-control form-control-lg border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                       value="{{ old('email') }}"
                                                       placeholder="Nhập email của bạn"
                                                       required
                                                       autofocus>
                                            </div>
                                        </div>

                                        <div class="pull-xs-left mb-3">
                                            <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                                GỬI LIÊN KẾT ĐẶT LẠI MẬT KHẨU
                                            </button>
                                        </div>

                                        <div class="btn_boz_khac">
                                            <div class="btn_khac w-100 d-flex justify-content-between align-items-center">
                                                <a href="{{ route('login') }}" class="btn-link-style">
                                                    &larr; Quay lại đăng nhập
                                                </a>
                                                <a href="{{ route('register') }}" class="btn-link-style">
                                                    Đăng ký tài khoản
                                                </a>
                                            </div>
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
</x-guest-layout>
