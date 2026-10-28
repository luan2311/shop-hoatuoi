@extends('layouts.admin')

@section('title', 'Quản lý Hóa Đơn')

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ HÓA ĐƠN</h1>

<form action="{{ route('admin.orders.index') }}" method="GET" class="form-inline mb-3">
    <div class="form-group">
        <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm theo tên hoặc tài khoản..." />
    </div>
    <div class="form-group ml-2">
        <select name="status" class="form-control" onchange="this.form.submit()">
            <option value="">-- Tất cả trạng thái --</option>
            @foreach(\App\Models\Order::STATUSES as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
    @if(request('searchString') || request('q') || request('status'))
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
    @endif
</form>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th>Mã HĐ</th>
                    <th>Tên Người Nhận</th>
                    <th>Tài Khoản</th>
                    <th>Địa Chỉ</th>
                    <th>Ngày Đặt</th>
                    <th>Trạng Thái</th>
                    <th>Tổng Tiền</th>
                    <th style="min-width: 170px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $item)
                    <tr>
                        <td>
                            <strong class="text-info">{{ $item->order_code }}</strong>
                        </td>
                        <td>{{ $item->customer['name'] ?? '—' }}</td>
                        <td>{{ $item->user->name ?? ($item->customer['email'] ?? 'Khách vãng lai') }}</td>
                        <td>
                            <span data-toggle="tooltip" data-placement="top" title="{{ $item->customer['address'] ?? '' }}" style="cursor: help;">
                                {{ Str::limit($item->customer['address'] ?? '—', 30) }}
                            </span>
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if ($item->status == 'Pending')
                                <span class="status-pending">{{ $item->status }}</span>
                            @elseif ($item->status == 'Completed')
                                <span class="status-completed">{{ $item->status }}</span>
                            @elseif ($item->status == 'Cancelled')
                                <span class="badge badge-danger">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-info">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td><strong>{{ number_format((float)($item->total_amount ?? $item->total), 0, ',', '.') }}đ</strong></td>
                        <td>
                            <a href="{{ route('admin.orders.show', $item->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>

                            @if ($item->status != 'Completed' && $item->status != 'Cancelled')
                                <form method="POST" action="{{ route('admin.orders.complete', $item->id) }}" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm ml-1" onclick="return confirm('Bạn có chắc đơn hàng này đã thanh toán?');">
                                        Completed
                                    </button>
                                </form>
                            @elseif ($item->status == 'Completed')
                                <span class="text-success font-weight-bold ml-1 small">✓ Hoàn thành</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy đơn hàng nào phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Trang {{ $orders->currentPage() }} / {{ $orders->lastPage() }} (Tổng cộng: {{ $orders->total() }} hóa đơn)
    </div>
    <nav aria-label="Page navigation">
        {{ $orders->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection
