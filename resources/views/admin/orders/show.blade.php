@extends('layouts.admin')

@section('title', 'Chi tiết Hóa Đơn')

@section('content')
<h2 class="text-center mb-4 font-weight-bold" style="color: #333;">CHI TIẾT HÓA ĐƠN</h2>

<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <div class="row">
        <div class="col-md-6">
            <h4 class="font-weight-bold text-dark">Hóa Đơn #{{ $order->order_code }}</h4>
            <hr />
            <dl class="row">
                <dt class="col-sm-4 text-muted">Tên Người Nhận</dt>
                <dd class="col-sm-8 font-weight-bold">{{ $order->customer['name'] ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Số Điện Thoại</dt>
                <dd class="col-sm-8">{{ $order->customer['phone'] ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8">{{ $order->customer['email'] ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Địa Chỉ Giao Hàng</dt>
                <dd class="col-sm-8">{{ $order->customer['address'] ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Ghi Chú</dt>
                <dd class="col-sm-8">{{ empty($order->customer['note']) ? 'Không có' : $order->customer['note'] }}</dd>
            </dl>
        </div>

        <div class="col-md-6">
            <h4 class="font-weight-bold text-dark">Thông Tin Đơn Hàng</h4>
            <hr />
            <dl class="row">
                <dt class="col-sm-4 text-muted">Tài Khoản</dt>
                <dd class="col-sm-8">{{ $order->user->name ?? ($order->customer['email'] ?? 'Khách vãng lai') }}</dd>

                <dt class="col-sm-4 text-muted">Ngày Đặt</dt>
                <dd class="col-sm-8">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '—' }}</dd>

                <dt class="col-sm-4 text-muted">Phương Thức TT</dt>
                <dd class="col-sm-8">
                    @if (($order->payment_method ?? 'COD') == 'COD')
                        <span class="badge badge-warning text-dark font-weight-bold">Thanh toán khi nhận hàng (COD)</span>
                    @else
                        <span class="badge badge-success">Thanh toán online - {{ $order->payment_method }}</span>
                    @endif
                </dd>

                <dt class="col-sm-4 text-muted">Trạng Thái</dt>
                <dd class="col-sm-8">
                    @if ($order->status == 'Pending')
                        <span class="status-pending">{{ $order->status }}</span>
                    @elseif ($order->status == 'Completed')
                        <span class="status-completed">{{ $order->status }}</span>
                    @elseif ($order->status == 'Cancelled')
                        <span class="badge badge-danger">{{ $order->status }}</span>
                    @else
                        <span class="badge badge-info">{{ $order->status }}</span>
                    @endif
                </dd>

                <dt class="col-sm-4 text-muted">Tổng Tiền</dt>
                <dd class="col-sm-8"><strong style="color: #d70018; font-size: 1.3em;">{{ number_format((float)($order->total_amount ?? $order->total), 0, ',', '.') }} VNĐ</strong></dd>
            </dl>

            <!-- Status Transition Form -->
            <form method="POST" action="{{ route('admin.orders.status', $order->id) }}" class="form-inline mt-3 pt-3 border-top">
                @csrf
                @method('PATCH')
                <label for="status" class="mr-2 font-weight-bold small">Cập nhật trạng thái:</label>
                <select name="status" id="status" class="form-control form-control-sm mr-2">
                    @foreach(\App\Models\Order::STATUSES as $st)
                        <option value="{{ $st }}" @selected($order->status === $st)>{{ $st }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">Cập nhật</button>
            </form>
        </div>
    </div>

    <h4 class="mt-4 font-weight-bold text-dark">Chi Tiết Sản Phẩm</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped mt-2">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 80px;">Ảnh</th>
                    <th>Sản Phẩm</th>
                    <th class="text-center">Số Lượng</th>
                    <th class="text-right">Đơn Giá</th>
                    <th class="text-right">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->items ?? [] as $item)
                    <tr>
                        <td class="text-center">
                            @php
                                $imgSrc = \App\Models\Product::resolveItemImageUrl($item['image'] ?? null, $item['product_code'] ?? null);
                            @endphp
                            @if($imgSrc)
                                <img src="{{ $imgSrc }}" alt="{{ $item['product_name'] ?? '' }}" class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;" />
                            @else
                                <div class="badge badge-light border">No image</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item['product_name'] ?? $item['product_code'] ?? 'Hoa tươi' }}</strong>
                            @if(!empty($item['product_code']))
                                <div class="small text-muted">Mã: {{ $item['product_code'] }}</div>
                            @endif
                        </td>
                        <td class="text-center font-weight-bold">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="text-right">{{ number_format((float)($item['price'] ?? 0), 0, ',', '.') }} VNĐ</td>
                        <td class="text-right">
                            <strong>{{ number_format((float)($item['subtotal'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1))), 0, ',', '.') }} VNĐ</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Không có dữ liệu mặt hàng</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <td colspan="4" class="text-right font-weight-bold">Tổng cộng:</td>
                    <td class="text-right">
                        <strong style="color: #d70018; font-size: 1.2em;">
                            {{ number_format((float)($order->total_amount ?? $order->total), 0, ',', '.') }} VNĐ
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
            &laquo; Trở về danh sách
        </a>
    </div>
</div>
@endsection
