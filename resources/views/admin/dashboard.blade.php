@extends('layouts.admin')

@section('title', 'Tổng quan quản trị')

@push('styles')
<style>
    .chart-card, .table-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 22px;
        margin-bottom: 24px;
    }

    .chart-card h4, .table-container h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f3f5;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .date-filter {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .date-filter .form-group {
        flex: 1;
        margin-bottom: 0;
    }

    .date-filter label {
        font-size: 12px;
        font-weight: 600;
        color: #718096;
        margin-bottom: 4px;
        display: block;
    }

    .date-filter input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .date-filter input:focus {
        border-color: #537453;
        outline: none;
        box-shadow: 0 0 0 3px rgba(83, 116, 83, 0.15);
    }

    .date-filter input.error {
        border-color: #dc3545;
    }

    .date-error-message {
        color: #dc3545;
        font-size: 11px;
        margin-top: 4px;
        display: none;
    }

    .date-error-message.show {
        display: block;
    }

    #chartContainer {
        position: relative;
        min-height: 280px;
    }

    /* Status Badges */
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-completed {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #664d03;
    }

    .status-processing {
        background-color: #cff4fc;
        color: #055160;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #842029;
    }

    /* Top Selling Card */
    .top-product-card {
        border: 1px solid #edf2f7;
        border-radius: 10px;
        padding: 14px 16px;
        background: #fafbfc;
        transition: all 0.2s ease;
    }

    .top-product-card:hover {
        background: #fff;
        border-color: #537453;
        box-shadow: 0 4px 12px rgba(83, 116, 83, 0.1);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1" style="color: #1e3926;">Tổng quan quản trị</h3>
            <p class="text-muted mb-0">Hệ thống quản lý và thống kê kinh doanh cửa hàng hoa tươi Góc Hoa Xinh</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-success shadow-sm btn-sm px-3" style="border-color: #537453; color: #537453;">
                <i class="fas fa-plus me-1"></i> Thêm danh mục hoa
            </a>
            <a href="{{ url('/admin/products/create') }}" class="btn btn-success shadow-sm btn-sm px-3" style="background-color: #537453; border-color: #537453;">
                <i class="fas fa-plus-circle me-1"></i> Thêm sản phẩm mới
            </a>
        </div>
    </div>

    <!-- 4 Summary Stats Cards (Ported from C# Dashboard) -->
    <div class="row g-3 mb-4">
        <!-- Tổng số sản phẩm -->
        <div class="col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="icon-box" style="background-color: #e3342f;">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalProducts) }}</div>
                    <div class="stat-title">Tổng số sản phẩm</div>
                    <a href="{{ url('/admin/products') }}" class="text-decoration-none small text-danger fw-semibold">
                        Xem sản phẩm &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Tổng người dùng -->
        <div class="col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="icon-box" style="background-color: #4dc0b5;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalUsers) }}</div>
                    <div class="stat-title">Tổng người dùng</div>
                    <a href="{{ url('/admin/users') }}" class="text-decoration-none small text-info fw-semibold">
                        Quản lý thành viên &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Đơn giao thành công / Tổng đơn -->
        <div class="col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="icon-box" style="background-color: #3490dc;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($completedOrders ?? $totalOrders) }}</div>
                    <div class="stat-title">Đơn giao thành công</div>
                    <a href="{{ url('/admin/orders') }}" class="text-decoration-none small text-primary fw-semibold">
                        Tổng {{ $totalOrders }} đơn hàng &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Tổng doanh thu -->
        <div class="col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="icon-box" style="background-color: #ffc107;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value text-truncate">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
                    <div class="stat-title">Tổng doanh thu</div>
                    <span class="badge bg-success-subtle text-success small">Đã đối soát</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Recent Orders (Col 7) and Revenue Chart (Col 5) -->
    <div class="row">
        <!-- Đơn hàng gần đây -->
        <div class="col-lg-7 mb-4">
            <div class="table-container h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 border-0 p-0">
                        <i class="fas fa-clipboard-list text-primary"></i> Đơn hàng gần đây
                    </h4>
                    <a href="{{ url('/admin/orders') }}" class="btn btn-sm btn-light border text-decoration-none">
                        Tất cả đơn hàng
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã HĐ</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="fw-bold text-secondary">#{{ $order->id ?? $order->order_code }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $order->receiver_name ?? ($order->user->name ?? 'Khách vãng lai') }}</div>
                                        <div class="small text-muted">{{ $order->receiver_phone ?? ($order->user->email ?? '') }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $order->created_at ? $order->created_at->format('d/m/Y') : '---' }}</td>
                                    <td class="fw-bold text-success">{{ number_format($order->total ?? $order->total_amount ?? 0, 0, ',', '.') }} đ</td>
                                    <td class="text-center">
                                        @php
                                            $st = $order->status ?? 'Pending';
                                            $badgeClass = match($st) {
                                                'Completed' => 'status-completed',
                                                'Processing' => 'status-processing',
                                                'Shipped' => 'status-processing',
                                                'Cancelled' => 'status-cancelled',
                                                default => 'status-pending',
                                            };
                                            $label = match($st) {
                                                'Completed' => 'Hoàn thành',
                                                'Processing' => 'Đang xử lý',
                                                'Shipped' => 'Đang giao',
                                                'Cancelled' => 'Đã hủy',
                                                default => 'Chờ duyệt',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $badgeClass }}">{{ $label }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-box-open fs-2 text-secondary mb-2 d-block"></i>
                                        Chưa có đơn hàng nào được ghi nhận.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu với Date Filter (Ported từ C# Dashboard) -->
        <div class="col-lg-5 mb-4">
            <div class="chart-card h-100">
                <h4>
                    <i class="fas fa-chart-line text-success"></i> Biểu đồ doanh thu
                </h4>

                <div class="date-filter">
                    <div class="form-group">
                        <label for="fromDate">Từ ngày</label>
                        <input type="date" id="fromDate" class="form-control" />
                        <small class="date-error-message" id="fromDateError"></small>
                    </div>
                    <div class="form-group">
                        <label for="toDate">Đến ngày</label>
                        <input type="date" id="toDate" class="form-control" />
                        <small class="date-error-message" id="toDateError"></small>
                    </div>
                </div>

                <div id="chartContainer">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Top Selling Products & Danh mục hoa -->
    <div class="row">
        <!-- Top sản phẩm bán chạy -->
        <div class="col-lg-7 mb-4">
            <div class="table-container h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 border-0 p-0">
                        <i class="fas fa-fire text-danger"></i> Sản phẩm bán chạy
                    </h4>
                    <span class="text-muted small">Top mặt hàng được đặt nhiều nhất</span>
                </div>

                <div class="row g-3">
                    @forelse($topSellingProducts as $product)
                        <div class="col-md-6">
                            <div class="top-product-card d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <div class="fw-bold text-dark mb-1 text-truncate" style="max-width: 220px;" title="{{ $product->product_name }}">
                                        {{ $product->product_name }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-check-circle text-success me-1"></i>Đã bán: <strong class="text-dark">{{ $product->quantity }}</strong> bó/kệ
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-success rounded-pill px-3 py-2 fw-bold" style="background-color: #537453 !important;">
                                        {{ number_format($product->total_amount, 0, ',', '.') }} đ
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-3 text-center text-muted">
                            Chưa có dữ liệu thống kê sản phẩm bán chạy.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Phân loại danh mục hoa -->
        <div class="col-lg-5 mb-4">
            <div class="table-container h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 border-0 p-0">
                        <i class="fas fa-tags text-warning"></i> Danh mục hoa ({{ $totalCategories }})
                    </h4>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-success">
                        Quản lý &rarr;
                    </a>
                </div>

                <ul class="list-group list-group-flush">
                    @forelse($categoriesWithCounts ?? [] as $cat)
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-leaf text-success small"></i>
                                <span class="fw-semibold text-dark">{{ $cat->name }}</span>
                                <span class="badge bg-light text-secondary border small">{{ $cat->slug }}</span>
                            </div>
                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1">
                                {{ $cat->products_count ?? $cat->products()->count() }} sản phẩm
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted text-center py-3">
                            Chưa có danh mục nào.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let revenueChartInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        const fromDateInput = document.getElementById('fromDate');
        const toDateInput = document.getElementById('toDate');

        // Set default dates (30 days ago to today)
        const today = new Date();
        const pastDate = new Date();
        pastDate.setDate(today.getDate() - 30);

        if (fromDateInput && toDateInput) {
            fromDateInput.value = formatDateForInput(pastDate);
            toDateInput.value = formatDateForInput(today);

            loadRevenueChart();

            let debounceTimer = null;
            [fromDateInput, toDateInput].forEach(input => {
                input.addEventListener('change', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        if (validateDateRange()) {
                            loadRevenueChart();
                        }
                    }, 300);
                });
            });
        }
    });

    function formatDateForInput(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function validateDateRange() {
        const fromVal = document.getElementById('fromDate').value;
        const toVal = document.getElementById('toDate').value;
        const toError = document.getElementById('toDateError');
        const toInput = document.getElementById('toDate');

        toInput.classList.remove('error');
        toError.classList.remove('show');
        toError.textContent = '';

        if (fromVal && toVal && new Date(fromVal) > new Date(toVal)) {
            toInput.classList.add('error');
            toError.textContent = 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu';
            toError.classList.add('show');
            return false;
        }
        return true;
    }

    function loadRevenueChart() {
        const fromVal = document.getElementById('fromDate').value;
        const toVal = document.getElementById('toDate').value;
        const url = `{{ route('admin.dashboard.revenue') }}?fromDate=${fromVal}&toDate=${toVal}`;

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    renderChart(res.data);
                }
            })
            .catch(err => {
                console.error("Lỗi khi tải dữ liệu biểu đồ doanh thu:", err);
            });
    }

    function renderChart(data) {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;

        const labels = data.map(item => item.date);
        const revenues = data.map(item => item.revenue);

        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(83, 116, 83, 0.4)');
        gradient.addColorStop(1, 'rgba(83, 116, 83, 0.0)');

        revenueChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenues,
                    borderColor: '#537453',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#537453',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const val = context.raw || 0;
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            font: { size: 11 },
                            callback: function (val) {
                                if (val >= 1000000) return (val / 1000000) + ' Tr';
                                if (val >= 1000) return (val / 1000) + ' k';
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection
