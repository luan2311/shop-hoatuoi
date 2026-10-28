@extends('layouts.admin')

@section('title', 'Quản lý Tài Khoản')

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ TÀI KHOẢN</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="{{ route('admin.users.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm theo tên đăng nhập hoặc email..." />
        </div>
        <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
        @endif
    </form>
    <p class="text-right mb-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="fas fa-user-plus mr-1"></i> Tạo tài khoản mới
        </a>
    </p>
</div>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th>Họ Tên</th>
                    <th>Tên Đăng Nhập</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="min-width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $item)
                    @php
                        $isAdmin = $item->isAdmin();
                        $isActive = $item->is_active ?? true;
                    @endphp
                    <tr>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->email }}</td>
                        <td>
                            @if($isActive)
                                <span class="badge badge-success">Hoạt động</span>
                            @else
                                <span class="badge badge-secondary">Vô hiệu hóa</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if (! $isAdmin)
                                @if ($isActive)
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $item) }}" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Bạn có chắc chắn muốn vô hiệu hóa tài khoản này không?');">
                                            Vô hiệu hóa
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $item) }}" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('Bạn có chắc chắn muốn kích hoạt lại tài khoản này không?');">
                                            Kích hoạt
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="badge badge-primary px-2 py-1">Admin</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Không tìm thấy tài khoản nào phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Trang {{ $users->currentPage() }} / {{ $users->lastPage() }} (Tổng: {{ $users->total() }} tài khoản)
    </div>
    <nav aria-label="Page navigation">
        {{ $users->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection
