@extends('layouts.app')

@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="bg-gray">
    <section class="section">
        <div class="container">
            <div class="page_register">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xl-4 offset-0 offset-xl-4 offset-lg-4 offset-md-3 col-12 account-animate">
                        <div class="page-register pagecustome clearfix">
                            <div class="wpx">
                                <h1 class="title_heads a-center"><span>Đăng ký</span></h1>

                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    @if ($errors->any())
                                        <div class="alert alert-danger" style="display: block; margin-bottom: 15px; font-size: 14px;">
                                            @foreach ($errors->all() as $error)
                                                <p style="margin: 0;">{{ $error }}</p>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="form-signup clearfix">
                                        <!-- Tên đăng nhập -->
                                        <fieldset class="form-group">
                                            <label for="TenDangNhap" class="form-label">
                                                Tên đăng nhập <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   name="TenDangNhap"
                                                   id="TenDangNhap"
                                                   class="form-control form-control-lg"
                                                   placeholder="Nhập tên đăng nhập"
                                                   value="{{ old('TenDangNhap', old('name')) }}"
                                                   required />
                                            <span class="text-danger" data-valmsg-for-tdn="TenDangNhap" style="display:none; font-size: 13px; margin-top: 5px;"></span>
                                        </fieldset>

                                        <!-- Email -->
                                        <fieldset class="form-group">
                                            <label for="Email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email"
                                                   name="Email"
                                                   id="Email"
                                                   class="form-control form-control-lg"
                                                   placeholder="Nhập địa chỉ email hợp lệ"
                                                   value="{{ old('Email', old('email')) }}"
                                                   required />
                                            <span class="text-danger" data-valmsg-for="Email" style="display:none; font-size: 13px; margin-top: 5px;"></span>
                                        </fieldset>

                                        <!-- Tên hiển thị -->
                                        <fieldset class="form-group">
                                            <label for="TenHienThi" class="form-label">
                                                Tên hiển thị <span class="text-muted fw-normal" style="font-size: 1.2rem;">(không bắt buộc)</span>
                                            </label>
                                            <input type="text"
                                                   name="TenHienThi"
                                                   id="TenHienThi"
                                                   class="form-control form-control-lg"
                                                   placeholder="Nhập tên hiển thị (ví dụ: Nguyễn Văn A)"
                                                   value="{{ old('TenHienThi') }}" />
                                        </fieldset>

                                        <!-- Mật khẩu -->
                                        <fieldset class="form-group">
                                            <label for="MatKhau" class="form-label">
                                                Mật khẩu <span class="text-danger">*</span>
                                            </label>
                                            <input type="password"
                                                   name="MatKhau"
                                                   id="MatKhau"
                                                   class="form-control form-control-lg"
                                                   placeholder="Nhập mật khẩu (tối thiểu 8 ký tự)"
                                                   required />
                                            <span class="text-danger" data-valmsg-for="MatKhau" style="display:none; font-size: 13px; margin-top: 5px;"></span>
                                        </fieldset>

                                        <!-- Xác nhận mật khẩu -->
                                        <fieldset class="form-group">
                                            <label for="XacNhanMatKhau" class="form-label">
                                                Xác nhận mật khẩu <span class="text-danger">*</span>
                                            </label>
                                            <input type="password"
                                                   name="XacNhanMatKhau"
                                                   id="XacNhanMatKhau"
                                                   class="form-control form-control-lg"
                                                   placeholder="Nhập lại mật khẩu"
                                                   required />
                                            <span class="text-danger" data-valmsg-for="XacNhanMatKhau" style="display:none; font-size: 13px; margin-top: 5px;"></span>
                                        </fieldset>

                                        <div class="action-btn-submit">
                                            <input class="btn btn-primary btn-style" type="submit" value="ĐĂNG KÝ" />
                                            <button type="submit" style="position: absolute; top: 0; left: -10px; width: 1px; height: 1px; opacity: 0; border: none; padding: 0; margin: 0; z-index: 10;" tabindex="-1"></button>
                                        </div>

                                        <div class="btn_boz_khac">
                                            <div class="btn_khac">
                                                <span>Đã có tài khoản?</span>
                                                <a href="{{ route('login') }}" class="btn-link-style btn-register" title="Đăng nhập">Đăng nhập</a>
                                            </div>
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
@endsection

@push('scripts')
<script>
    (function () {
        function initValidation() {
            var form = document.querySelector('form[action*="register"]') || document.querySelector('.page_register form');
            var usernameInput = document.querySelector('input[name="TenDangNhap"]');
            var emailInput = document.querySelector('input[name="Email"]');
            var passwordInput = document.querySelector('input[name="MatKhau"]');
            var confirmPasswordInput = document.querySelector('input[name="XacNhanMatKhau"]');

            if (!form) return;

            // Trim khoảng trắng cho username
            if (usernameInput) {
                usernameInput.addEventListener('blur', function () {
                    this.value = this.value.trim();
                });
            }

            function showError(inputElement, fieldName, message) {
                if (inputElement) {
                    inputElement.classList.add('is-invalid');
                    inputElement.classList.remove('is-valid');
                }
                var span = document.querySelector("[data-valmsg-for='" + fieldName + "']") || document.querySelector("[data-valmsg-for-tdn='" + fieldName + "']");
                if (span) {
                    span.textContent = message;
                    span.style.display = 'block';
                }
                return false;
            }

            function clearError(inputElement, fieldName) {
                if (inputElement) {
                    inputElement.classList.remove('is-invalid');
                    inputElement.classList.add('is-valid');
                }
                var span = document.querySelector("[data-valmsg-for='" + fieldName + "']") || document.querySelector("[data-valmsg-for-tdn='" + fieldName + "']");
                if (span) {
                    span.textContent = '';
                    span.style.display = 'none';
                }
                return true;
            }

            function validateEmail() {
                if (!emailInput) return true;
                var val = emailInput.value.trim();
                if (!val) {
                    return showError(emailInput, 'Email', "Vui lòng nhập Email.");
                }
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(val)) {
                    return showError(emailInput, 'Email', "Email không hợp lệ (ví dụ: client@example.com)");
                }
                return clearError(emailInput, 'Email');
            }

            if (emailInput) {
                emailInput.addEventListener('blur', validateEmail);
                emailInput.addEventListener('input', validateEmail);
            }

            function validatePassword() {
                if (!passwordInput) return true;
                var val = passwordInput.value;
                if (!val) {
                    return showError(passwordInput, 'MatKhau', "Vui lòng nhập mật khẩu.");
                }
                if (val.length < 8) {
                    return showError(passwordInput, 'MatKhau', "Mật khẩu phải từ 8 ký tự trở lên.");
                }
                return clearError(passwordInput, 'MatKhau');
            }

            if (passwordInput) {
                passwordInput.addEventListener('blur', validatePassword);
                passwordInput.addEventListener('input', validatePassword);
            }

            function validateConfirmPassword() {
                if (!confirmPasswordInput || !passwordInput) return true;
                var val = confirmPasswordInput.value;
                var pwd = passwordInput.value;
                if (val !== pwd) {
                    return showError(confirmPasswordInput, 'XacNhanMatKhau', "Mật khẩu xác nhận không khớp.");
                }
                return clearError(confirmPasswordInput, 'XacNhanMatKhau');
            }

            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
                confirmPasswordInput.addEventListener('input', validateConfirmPassword);
            }

            form.addEventListener('submit', function (e) {
                if (usernameInput) {
                    usernameInput.value = usernameInput.value.trim();
                }
                var isEmailValid = validateEmail();
                var isPasswordValid = validatePassword();
                var isConfirmValid = validateConfirmPassword();

                if (!isEmailValid || !isPasswordValid || !isConfirmValid) {
                    e.preventDefault();
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initValidation);
        } else {
            initValidation();
        }
    })();
</script>
@endpush
