@extends('layouts.admin')

@section('title', 'Tổng quan quản trị')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Tổng quan quản trị</h3>
            <p class="text-muted mb-0">Thống kê hoạt động kinh doanh và dữ liệu shop hoa tươi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/admin/categories/create') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Thêm danh mục
            </a>
            <a href="{{ url('/admin/products/create') }}" class="btn btn-primary btn-sm" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
            </a>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Danh mục hoa</span>
                        <h3 class="fw-bold my-1 text-dark">{{ $totalCategories }}</h3>
                        <a href="{{ url('/admin/categories') }}" class="text-decoration-none small text-primary">Xem chi tiết &rarr;</a>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Sản phẩm hoa</span>
                        <h3 class="fw-bold my-1 text-dark">{{ $totalProducts }}</h3>
                        <a href="{{ url('/admin/products') }}" class="text-decoration-none small text-success">Xem chi tiết &rarr;</a>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-flower2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Tổng đơn hàng</span>
                        <h3 class="fw-bold my-1 text-dark">{{ $totalOrders }}</h3>
                        <a href="{{ url('/admin/orders') }}" class="text-decoration-none small text-warning">Xem chi tiết &rarr;</a>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-cart-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Khách hàng</span>
                        <h3 class="fw-bold my-1 text-dark">{{ $totalUsers }}</h3>
                        <a href="{{ url('/admin/users') }}" class="text-decoration-none small text-secondary">Xem chi tiết &rarr;</a>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Sections -->
    <div class="row g-4">
        <!-- Đơn hàng mới -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Đơn hàng mới nhất</h5>
                    <a href="{{ url('/admin/orders') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="fw-semibold">#{{ $order->id }}</td>
                                        <td>{{ $order->receiver_name ?? ($order->user->name ?? 'Khách') }}</td>
                                        <td class="text-danger fw-semibold">{{ number_format($order->total, 0, ',', '.') }} đ</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $order->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Chưa có đơn hàng nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sản phẩm mới -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Sản phẩm mới thêm</h5>
                    <a href="{{ url('/admin/products') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentProducts as $prod)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2">
                                <div>
                                    <div class="fw-semibold">{{ $prod->name }}</div>
                                    <small class="text-muted">{{ $prod->category->name ?? 'Chưa phân loại' }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-danger fw-bold small">{{ number_format($prod->price, 0, ',', '.') }} đ</div>
                                    <small class="text-muted">Kho: {{ $prod->stock }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Chưa có sản phẩm nào</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
