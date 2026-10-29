@extends('layouts.app')

@section('title', 'Thông tin tài khoản - ' . config('app.name', 'Shop Hoa Tươi'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb_style.css') }}">
    <style>
        .page_customer_account {
            padding: 30px 0 60px;
            background: #fbfbfb;
        }
        .col-left-ac .block-account {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .col-left-ac .block-account .title-account {
            font-size: 18px;
            font-weight: 700;
            color: #537453;
            text-transform: uppercase;
            margin-bottom: 8px;
            padding-bottom: 10px;
            border-bottom: 2px solid #537453;
        }
        .col-left-ac .block-account p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        .col-left-ac .block-account ul {
            padding-left: 0;
            list-style: none;
            margin-bottom: 0;
        }
        .col-left-ac .block-account ul li {
            margin-bottom: 10px;
        }
        .col-left-ac .block-account ul .title-info {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            border-radius: 6px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
        }
        .col-left-ac .block-account ul .title-info:hover,
        .col-left-ac .block-account ul .title-info.active {
            background-color: #f0f5f0;
            color: #537453;
            font-weight: 600;
        }
        .col-left-ac .block-account ul .title-info i {
            width: 22px;
            font-size: 15px;
        }
        .account-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .account-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 18px 24px;
        }
        .account-card .card-header h4 {
            font-size: 17px;
            font-weight: 700;
            color: #537453;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .account-card .card-body {
            padding: 24px;
        }
        .account-card .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }
        .account-card .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .account-card .form-control:focus {
            border-color: #537453;
            box-shadow: 0 0 0 3px rgba(83, 116, 83, 0.15);
        }
        .account-card .btn-style {
            background-color: #537453 !important;
            color: #fff !important;
            border: none;
            padding: 10px 24px;
            font-size: 14px !important;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }
        .account-card .btn-style:hover {
            background-color: #ea8725 !important;
            color: #fff !important;
        }
    </style>
@endpush

@section('content')
<div class="py-2 mb-3 breadcrumb_background bg-light">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="py-2 mb-0 breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Trang tài khoản</li>
            </ol>
        </nav>
    </div>
</div>

<section class="page_customer_account">
    <div class="container">
        <div class="row">
            <!-- Cột trái: Điều hướng tài khoản (Ported từ Index.cshtml repo cũ) -->
            <aside class="col-xs-12 col-sm-12 col-lg-3 col-left-ac mb-4">
                <div class="block-account">
                    <h5 class="title-account">Trang tài khoản</h5>
                    <p>
                        Xin chào, <span style="color:#01567f; font-weight: 600;">{{ $user->name ?: $user->username }}</span>!
                    </p>
                    <ul>
                        <li>
                            <a class="title-info active" href="{{ route('profile.edit') }}">
                                <i class="fa fa-user me-2 text-muted"></i>Thông tin tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="title-info" href="/account/orders">
                                <i class="fa fa-shopping-bag me-2 text-muted"></i>Đơn hàng của bạn
                            </a>
                        </li>
                        <li>
                            <a class="title-info" href="/wishlist">
                                <i class="fa fa-heart me-2 text-danger"></i>Danh sách yêu thích
                            </a>
                        </li>
                        <li>
                            <a class="title-info" href="#doi-mat-khau">
                                <i class="fa fa-key me-2 text-muted"></i>Đổi mật khẩu
                            </a>
                        </li>
                        <li>
                            <form method="post" action="{{ route('logout') }}" id="sidebarLogoutForm" style="display:inline;">
                                @csrf
                                <a class="title-info text-danger" href="#" onclick="document.getElementById('sidebarLogoutForm').submit(); return false;">
                                    <i class="fa fa-sign-out me-2"></i>Đăng xuất
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Cột phải: Thông tin cá nhân & Đổi mật khẩu -->
            <main class="col-xs-12 col-sm-12 col-lg-9 col-right-ac">
                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle me-2"></i>Cập nhật thông tin tài khoản thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle me-2"></i>Đổi mật khẩu thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Khối 1: Thông tin tài khoản -->
                <div class="account-card">
                    <div class="card-header">
                        <h4><i class="fa fa-id-card"></i> Thông tin cá nhân</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}"
                                           required
                                           placeholder="Nhập họ và tên" />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        Tên đăng nhập
                                    </label>
                                    <input type="text"
                                           id="username"
                                           class="form-control bg-light"
                                           value="{{ $user->username }}"
                                           disabled
                                           readonly />
                                    <small class="text-muted">Tên đăng nhập không thể thay đổi.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}"
                                           required
                                           placeholder="Nhập địa chỉ email" />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        Số điện thoại
                                    </label>
                                    <input type="tel"
                                           name="phone"
                                           id="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="Nhập số điện thoại liên hệ" />
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    Địa chỉ giao hàng mặc định
                                </label>
                                <input type="text"
                                       name="address"
                                       id="address"
                                       class="form-control @error('address') is-invalid @enderror"
                                       value="{{ old('address', $user->address) }}"
                                       placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố" />
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-style mt-2">
                                <i class="fa fa-save me-1"></i> Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Khối 2: Đổi mật khẩu (Ported từ Doi_mat_khau.cshtml repo cũ) -->
                <div class="account-card" id="doi-mat-khau">
                    <div class="card-header">
                        <h4><i class="fa fa-lock"></i> Đổi mật khẩu</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->updatePassword->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->updatePassword->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="update_password_current_password" class="form-label">
                                        Mật khẩu hiện tại <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           name="current_password"
                                           id="update_password_current_password"
                                           class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                                           placeholder="Nhập mật khẩu hiện tại"
                                           autocomplete="current-password"
                                           required />
                                    @if($errors->updatePassword->has('current_password'))
                                        <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="update_password_password" class="form-label">
                                        Mật khẩu mới <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           name="password"
                                           id="update_password_password"
                                           class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                                           placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)"
                                           autocomplete="new-password"
                                           required />
                                    @if($errors->updatePassword->has('password'))
                                        <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="update_password_password_confirmation" class="form-label">
                                        Xác nhận mật khẩu mới <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           name="password_confirmation"
                                           id="update_password_password_confirmation"
                                           class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                                           placeholder="Xác nhận lại mật khẩu mới"
                                           autocomplete="new-password"
                                           required />
                                    @if($errors->updatePassword->has('password_confirmation'))
                                        <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                                    @endif
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-style mt-2">
                                <i class="fa fa-key me-1"></i> Cập nhật mật khẩu
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Khối 3: Xóa tài khoản -->
                <div class="account-card border-danger-subtle">
                    <div class="card-header bg-light">
                        <h4 class="text-danger"><i class="fa fa-exclamation-triangle"></i> Xóa tài khoản</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Khi bạn xóa tài khoản, toàn bộ thông tin và dữ liệu sẽ bị xóa vĩnh viễn. Vui lòng cân nhắc kỹ trước khi thực hiện.
                        </p>

                        @if ($errors->userDeletion->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->userDeletion->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal" style="font-size: small; border-radius: 10px;">
                            <i class="fa fa-trash me-1"></i> Xóa tài khoản vĩnh viễn
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</section>
@endsection

@push('modals')
<!-- Modal xác nhận xóa tài khoản -->
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="confirmUserDeletionModalLabel">
                        <i class="fa fa-exclamation-triangle me-1"></i> Xác nhận xóa tài khoản
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Bạn có chắc chắn muốn xóa tài khoản này không? Vui lòng nhập mật khẩu tài khoản của bạn để xác nhận.
                    </p>
                    <div class="mb-3">
                        <label for="delete_account_password" class="form-label">
                            Mật khẩu xác nhận <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               id="delete_account_password"
                               class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                               placeholder="Nhập mật khẩu"
                               required />
                        @if($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger">Xóa tài khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if ($errors->userDeletion->isNotEmpty())
            var modalEl = document.getElementById('confirmUserDeletionModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                var myModal = new bootstrap.Modal(modalEl);
                myModal.show();
            }
        @endif
    });
</script>
@endpush
