<p>Mã đơn: <strong>{{ $order->order_code }}</strong></p>
<p>Trạng thái: {{ $order->status }}</p>
<p>Ngày đặt (UTC+7): {{ $order->order_date?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') ?? '—' }}</p>
<p>Người nhận: {{ $order->customer['name'] ?? '—' }} — {{ $order->customer['phone'] ?? '—' }}</p>
<p>Địa chỉ: {{ $order->customer['address'] ?? '—' }}</p>
<p>Email: {{ $order->customer['email'] ?? '—' }}</p>
<p>Ghi chú: {{ $order->customer['note'] ?? '—' }}</p>
<p>Thanh toán: {{ $order->payment_method }}</p>
<div class="overflow-x-auto">
    <table class="w-full min-w-[640px] text-left">
        <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead>
        <tbody>@foreach($order->items ?? [] as $item)
            <tr class="border-t"><td class="p-2">{{ $item['product_name'] }}</td><td class="p-2">{{ number_format((float) (string) $item['price'], 0, ',', '.') }} đ</td><td class="p-2">{{ $item['quantity'] }}</td><td class="p-2">{{ number_format((float) (string) $item['subtotal'], 0, ',', '.') }} đ</td></tr>
        @endforeach</tbody>
    </table>
</div>
@if($order->voucher_code)
    <p>Tạm tính: {{ number_format((float) (string) $order->subtotal_amount, 0, ',', '.') }} đ</p>
    <p>Voucher {{ $order->voucher_code }}: - {{ number_format((float) (string) $order->discount_amount, 0, ',', '.') }} đ</p>
@endif
<p class="font-semibold">Tổng tiền: {{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ</p>
@include('orders.partials.banking-qr')
