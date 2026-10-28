@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    .chart-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .chart-card h4 {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .date-filter {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .date-filter .form-group {
        flex: 1;
        margin-bottom: 0;
    }

    .date-filter label {
        font-size: 12px;
        font-weight: 500;
        color: #666;
        margin-bottom: 5px;
        display: block;
    }

    .date-filter input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .date-filter input:focus {
        border-color: #4a90e2;
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .date-filter input.error {
        border-color: #dc3545;
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    .date-error-message {
        color: #dc3545;
        font-size: 11px;
        margin-top: 5px;
        display: none;
    }

    .date-error-message.show {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #chartContainer {
        position: relative;
        min-height: 400px;
        padding: 10px 0;
    }

    .table-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .table-container h4 {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="icon" style="background-color: #e3342f">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div class="text-value">{{ $totalProducts }}</div>
                        <div class="text-muted">Tổng số sản phẩm</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="icon" style="background-color: #4dc0b5">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-value">{{ $totalUsers }}</div>
                        <div class="text-muted">Tổng người dùng</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="icon" style="background-color: #3490dc">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="text-value">{{ $completedOrders }}</div>
                        <div class="text-muted">Đơn giao thành công</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="icon" style="background-color: #ffc107">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="text-value">{{ number_format($totalRevenue, 0, ',', '.') }}</div>
                        <div class="text-muted">Tổng doanh thu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Revenue Chart -->
    <div class="row">
        <!-- Đơn hàng gần đây -->
        <div class="col-md-7">
            <div class="table-container">
                <h4>Đơn hàng gần đây</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã HĐ</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-info font-weight-bold">
                                            {{ $order->order_code }}
                                        </a>
                                    </td>
                                    <td>{{ $order->customer['name'] ?? ($order->user->name ?? 'Khách hàng') }}</td>
                                    <td>{{ $order->created_at ? $order->created_at->format('dd/MM/yyyy') : '—' }}</td>
                                    <td>{{ number_format((float)($order->total_amount ?? $order->total), 0, ',', '.') }}đ</td>
                                    <td>
                                        @if ($order->status == 'Pending')
                                            <span class="status-pending">{{ $order->status }}</span>
                                        @else
                                            <span class="status-completed">{{ $order->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Chưa có đơn hàng nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <div class="col-md-5">
            <div class="chart-card">
                <h4>Biểu đồ doanh thu</h4>
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

    <!-- Top Selling Products -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-container">
                <h4>Sản phẩm bán chạy</h4>
                <div class="row">
                    @forelse ($topSellingProducts as $product)
                        <div class="col-md-4 mb-2">
                            <div class="list-group-item d-flex justify-content-between align-items-center"
                                 style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px;">
                                <div>
                                    <strong style="font-size: 14px; color: #333">{{ $product->product_name }}</strong><br />
                                    <small style="color: #666">Đã bán: {{ $product->quantity }}</small>
                                </div>
                                <span class="badge badge-primary badge-pill" style="font-size: 13px; padding: 8px 12px">
                                    {{ number_format($product->total_amount, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">Chưa có số liệu sản phẩm bán chạy.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    var revenueChart;

    $(document).ready(function () {
        // Set default dates (last 30 days)
        var today = new Date();
        var lastMonth = new Date(today);
        lastMonth.setDate(lastMonth.getDate() - 30);

        $("#toDate").val(formatDate(today));
        $("#fromDate").val(formatDate(lastMonth));

        // Load initial chart
        loadRevenueChart();

        // Auto reload chart when date changes
        $("#fromDate, #toDate").on("change", function () {
            if (window.chartLoadTimeout) {
                clearTimeout(window.chartLoadTimeout);
            }

            window.chartLoadTimeout = setTimeout(function () {
                if (validateDates()) {
                    loadRevenueChart();
                }
            }, 300);
        });
    });

    function formatDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, "0");
        var day = String(date.getDate()).padStart(2, "0");
        return year + "-" + month + "-" + day;
    }

    function validateDates() {
        var fromDate = new Date($("#fromDate").val());
        var toDate = new Date($("#toDate").val());
        var $fromInput = $("#fromDate");
        var $toInput = $("#toDate");
        var $fromError = $("#fromDateError");
        var $toError = $("#toDateError");

        $fromInput.removeClass("error");
        $toInput.removeClass("error");
        $fromError.removeClass("show").text("");
        $toError.removeClass("show").text("");

        if (fromDate && toDate && fromDate > toDate) {
            $toInput.addClass("error");
            $toError.text("Ngày kết thúc phải sau ngày bắt đầu").addClass("show");

            setTimeout(function () {
                $("#toDate").val($("#fromDate").val());
                $toInput.removeClass("error");
                $toError.removeClass("show");
            }, 2000);

            return false;
        }

        if (fromDate && toDate) {
            var diffTime = Math.abs(toDate - fromDate);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 365) {
                $fromInput.addClass("error");
                $toInput.addClass("error");
                $toError.text("Khoảng thời gian không được vượt quá 365 ngày").addClass("show");
                setTimeout(function () {
                    var newFromDate = new Date(toDate);
                    newFromDate.setDate(newFromDate.getDate() - 365);
                    $("#fromDate").val(formatDate(newFromDate));
                    $fromInput.removeClass("error");
                    $toInput.removeClass("error");
                    $toError.removeClass("show");
                }, 2000);

                return false;
            }
        }

        return true;
    }

    function loadRevenueChart() {
        var fromDate = $("#fromDate").val();
        var toDate = $("#toDate").val();

        if (!fromDate || !toDate) {
            return;
        }

        var fromDateObj = new Date(fromDate);
        var toDateObj = new Date(toDate);

        if (fromDateObj > toDateObj) {
            return;
        }

        var url = '{{ route("admin.dashboard.revenue") }}';

        $.ajax({
            url: url,
            type: "GET",
            data: {
                fromDate: fromDate,
                toDate: toDate,
            },
            success: function (response) {
                if (response.success) {
                    renderChart(response.data);
                } else {
                    showEmptyChart();
                }
            },
            error: function () {
                showEmptyChart();
            },
        });
    }

    function showEmptyChart() {
        var chartContainer = document.getElementById("chartContainer");
        chartContainer.innerHTML =
            '<div style="text-align: center; padding: 50px; color: #999;">' +
            '<i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 15px;"></i><br>' +
            "<strong>Không có dữ liệu</strong><br>" +
            "<small>Chưa có doanh thu trong khoảng thời gian này</small>" +
            "</div>" +
            '<canvas id="revenueChart" style="display: none;"></canvas>';
    }

    function renderChart(data) {
        var chartContainer = document.getElementById("chartContainer");

        if (revenueChart) {
            revenueChart.destroy();
            revenueChart = null;
        }

        if (!data || data.length === 0) {
            showEmptyChart();
            return;
        }

        chartContainer.innerHTML = '<canvas id="revenueChart"></canvas>';

        var canvas = document.getElementById("revenueChart");
        if (!canvas) return;

        var ctx = canvas.getContext("2d");

        var labels = data.map(function (item) { return item.date || item.Date; });
        var revenues = data.map(function (item) { return item.revenue || item.Revenue; });

        var gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, "rgba(74, 144, 226, 0.3)");
        gradient.addColorStop(0.5, "rgba(74, 144, 226, 0.15)");
        gradient.addColorStop(1, "rgba(74, 144, 226, 0.02)");

        revenueChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Doanh thu",
                        data: revenues,
                        backgroundColor: gradient,
                        borderColor: "#4A90E2",
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: "#4A90E2",
                        pointBorderColor: "#fff",
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: "#4A90E2",
                        pointHoverBorderColor: "#fff",
                        pointHoverBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                        padding: 12,
                        titleColor: "#fff",
                        titleFont: {
                            size: 13,
                            weight: "600",
                        },
                        bodyColor: "#fff",
                        bodyFont: {
                            size: 13,
                        },
                        borderColor: "#4A90E2",
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            title: function (context) {
                                return context[0].label;
                            },
                            label: function (context) {
                                return (
                                    new Intl.NumberFormat("vi-VN").format(context.parsed.y) +
                                    " VNĐ"
                                );
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 11,
                            },
                            color: "#666",
                            maxRotation: 45,
                            minRotation: 45,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                            drawBorder: false,
                        },
                        ticks: {
                            font: {
                                size: 11,
                            },
                            color: "#666",
                            callback: function (value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + "M";
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + "K";
                                }
                                return value;
                            },
                        },
                    },
                },
            },
        });
    }
</script>
@endsection
