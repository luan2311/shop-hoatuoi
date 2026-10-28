@extends('layouts.admin')

@section('title', 'Danh sách Liên Hệ')

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">DANH SÁCH LIÊN HỆ</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="{{ route('admin.contacts.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm theo tên, email, sđt..." />
        </div>
        <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
        @endif
    </form>
</div>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th>Mã LH</th>
                    <th>Họ Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Nội dung</th>
                    <th style="min-width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contacts as $item)
                    <tr>
                        <td><strong>{{ $item->code ?? 'LH' }}</strong></td>
                        <td>{{ $item->full_name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ Str::limit($item->message, 45) }}</td>
                        <td>
                            <a href="{{ route('admin.contacts.show', $item) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>

                            <form method="POST" action="{{ route('admin.contacts.destroy', $item) }}" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa liên hệ này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm ml-1">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Không có thông tin liên hệ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Trang {{ $contacts->currentPage() }} / {{ $contacts->lastPage() }} (Tổng: {{ $contacts->total() }} liên hệ)
    </div>
    <nav aria-label="Page navigation">
        {{ $contacts->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection
