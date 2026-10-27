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
                                        <span>Đăng nhập</span>
                                    </h1>

                                    <!-- Session Status / Alert Messages -->
                                    @if (session('status'))
                                        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                                            <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger mb-3" style="font-size: 14px;">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Main Login Form -->
                                    <div id="login" class="section">
                                        <form method="POST" action="{{ route('login') }}" class="form-signup">
                                            @csrf

                                            <div class="form-group mb-3">
                                                <label for="email" class="form-label fw-semibold text-secondary small">Email đăng nhập <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="email"
                                                           id="email"
                                                           name="email"
                                                           class="form-control form-control-lg border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                           value="{{ old('email') }}"
                                                           placeholder="Nhập địa chỉ email của bạn"
                                                           required
                                                           autofocus
                                                           autocomplete="username">
                                                </div>
                                            </div>

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
                                                           placeholder="Nhập mật khẩu"
                                                           required
                                                           autocomplete="current-password">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                                    <label class="form-check-label text-secondary small" for="remember_me">
                                                        Ghi nhớ mật khẩu
                                                    </label>
                                                </div>
                                                <div>
                                                    <span class="quenmk" id="btnForgotPass" style="cursor: pointer; font-size: 14px;">Quên mật khẩu?</span>
                                                </div>
                                            </div>

                                            <div class="pull-xs-left mb-3">
                                                <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                                    ĐĂNG NHẬP
                                                </button>
                                            </div>

                                            <div class="btn_boz_khac">
                                                <div class="btn_khac w-100 d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">Chưa có tài khoản?</span>
                                                    <a href="{{ route('register') }}" class="btn-link-style btn-register" title="Đăng ký tài khoản">
                                                        Đăng ký tại đây
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Recover Password Section (Toggled dynamically like C# Dang_nhap.cshtml) -->
                                    <div class="h_recover" style="display:none;">
                                        <div class="text-center mb-3">
                                            <h4 class="fw-bold" style="color: var(--brand-primary);">Lấy lại mật khẩu</h4>
                                            <p class="text-muted small">Chúng tôi sẽ gửi cho bạn một liên kết qua email để kích hoạt việc đặt lại mật khẩu mới.</p>
                                        </div>

                                        <form method="POST" action="{{ route('password.email') }}" class="form-signup">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="email"
                                                           name="email"
                                                           id="recover-email"
                                                           class="form-control form-control-lg border-start-0 ps-0"
                                                           placeholder="Nhập email của bạn"
                                                           value="{{ old('email') }}"
                                                           required>
                                                </div>
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-style text-uppercase">
                                                    GỬI LIÊN KẾT XÁC NHẬN
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary quay_lai" style="border-radius: 8px; height: 42px;">
                                                    Hủy bỏ và quay lại
                                                </button>
                                            </div>
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginSection = document.getElementById('login');
            const recoverSection = document.querySelector('.h_recover');
            const mainTitle = document.querySelector('.title_heads span');
            const btnForgot = document.getElementById('btnForgotPass');
            const btnCancel = document.querySelector('.quay_lai');

            if (btnForgot && loginSection && recoverSection) {
                btnForgot.addEventListener('click', function () {
                    loginSection.style.display = 'none';
                    recoverSection.style.display = 'block';
                    if (mainTitle) mainTitle.textContent = 'Lấy lại mật khẩu';
                });
            }

            if (btnCancel && loginSection && recoverSection) {
                btnCancel.addEventListener('click', function () {
                    recoverSection.style.display = 'none';
                    loginSection.style.display = 'block';
                    if (mainTitle) mainTitle.textContent = 'Đăng nhập';
                });
            }
        });
    </script>
    @endpush
</x-guest-layout>
