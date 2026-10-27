<x-guest-layout>
    <div class="bg-gray py-5">
        <section class="section">
            <div class="container">
                <div class="page_register">
                    <div class="row justify-content-center">
                        <div class="col-lg-5 col-md-7 col-sm-10 col-12 account-animate">
                            <div class="page-register pagecustome clearfix">
                                <div class="wpx">
                                    <h1 class="title_heads a-center">
                                        <span>Đăng ký</span>
                                    </h1>

                                    @if ($errors->any())
                                        <div class="alert alert-danger mb-3" style="font-size: 14px;">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('register') }}" class="form-signup">
                                        @csrf

                                        <!-- Họ và tên / Tên hiển thị -->
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label fw-semibold text-secondary small">Họ và tên <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <input type="text"
                                                       id="name"
                                                       name="name"
                                                       class="form-control form-control-lg border-start-0 ps-0 @error('name') is-invalid @enderror"
                                                       value="{{ old('name') }}"
                                                       placeholder="Nhập họ và tên của bạn"
                                                       required
                                                       autofocus
                                                       autocomplete="name">
                                            </div>
                                        </div>

                                        <!-- Email -->
                                        <div class="form-group mb-3">
                                            <label for="email" class="form-label fw-semibold text-secondary small">Địa chỉ Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email"
                                                       id="email"
                                                       name="email"
                                                       class="form-control form-control-lg border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                       value="{{ old('email') }}"
                                                       placeholder="Nhập địa chỉ email hợp lệ"
                                                       required
                                                       autocomplete="username">
                                            </div>
                                        </div>

                                        <!-- Mật khẩu -->
                                        <div class="form-group mb-3">
                                            <label for="password" class="form-label fw-semibold text-secondary small">Mật khẩu <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password"
                                                       id="password"
                                                       name="password"
                                                       class="form-control form-control-lg border-start-0 ps-0 @error('password') is-invalid @enderror"
                                                       placeholder="Mật khẩu tối thiểu 8 ký tự"
                                                       required
                                                       autocomplete="new-password">
                                            </div>
                                        </div>

                                        <!-- Xác nhận mật khẩu -->
                                        <div class="form-group mb-4">
                                            <label for="password_confirmation" class="form-label fw-semibold text-secondary small">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
                                                <input type="password"
                                                       id="password_confirmation"
                                                       name="password_confirmation"
                                                       class="form-control form-control-lg border-start-0 ps-0"
                                                       placeholder="Nhập lại mật khẩu vừa nhập"
                                                       required
                                                       autocomplete="new-password">
                                            </div>
                                        </div>

                                        <div class="pull-xs-left mb-3">
                                            <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                                ĐĂNG KÝ TÀI KHOẢN
                                            </button>
                                        </div>

                                        <div class="btn_boz_khac">
                                            <div class="btn_khac w-100 d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Đã có tài khoản?</span>
                                                <a href="{{ route('login') }}" class="btn-link-style btn-register" title="Đăng nhập">
                                                    Đăng nhập tại đây
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
