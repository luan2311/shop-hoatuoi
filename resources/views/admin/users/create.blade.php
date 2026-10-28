@extends('layouts.admin')

@section('title', 'Tạo Tài Khoản Mới')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2 class="text-center font-weight-bold mb-4" style="color: #333;">TẠO TÀI KHOẢN MỚI</h2>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <strong>Có lỗi xảy ra! Vui lòng kiểm tra lại thông tin:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="mt-4">
        @csrf

        <div class="form-group row">
            <label for="username" class="col-md-2 col-form-label">Tên Đăng Nhập <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}" required placeholder="Nhập tên đăng nhập..." />
                @error('username') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="email" class="col-md-2 col-form-label">Email <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required placeholder="name@example.com" />
                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="password" class="col-md-2 col-form-label">Mật Khẩu <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="password" name="password" id="password" class="form-control" required placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)..." />
                @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="password_confirmation" class="col-md-2 col-form-label">Xác Nhận Mật Khẩu <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu..." />
            </div>
        </div>

        <div class="form-group row">
            <label for="name" class="col-md-2 col-form-label">Tên Hiển Thị <span class="text-danger">*</span></label>
            <div class="col-md-10">
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Họ và tên..." />
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="roles" class="col-md-2 col-form-label">Vai Trò</label>
            <div class="col-md-10">
                <select name="roles[]" id="roles" class="form-control" multiple style="height: 90px;">
                    <option value="User" selected>User (Khách hàng)</option>
                    <option value="Admin">Admin (Quản trị viên)</option>
                </select>
                <small class="form-text text-muted">Giữ Ctrl (hoặc Cmd) để chọn nhiều vai trò</small>
                @error('roles') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-md-10 offset-md-2">
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-user-check mr-1"></i> Tạo tài khoản
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ml-2">
                    Trở về danh sách
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
