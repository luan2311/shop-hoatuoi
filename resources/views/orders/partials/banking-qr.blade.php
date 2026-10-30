@if($order->payment_method === 'Banking' && $order->status !== 'Cancelled')
    <div class="banking-info-box">
        <p>Chuyển khoản theo đơn {{ $order->order_code }}: <strong>{{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ</strong></p>
        <img src="https://api.vietqr.io/image/970422-0898449950-compact2.jpg?{{ http_build_query(['amount' => (int) (string) $order->total_amount, 'addInfo' => $order->order_code, 'accountName' => 'TIEM HOA GOC HOA XINH'], '', '&', PHP_QUERY_RFC3986) }}"
             alt="VietQR Mã Thanh Toán" class="banking-qr-code" style="max-width: 240px; height: auto;">
        <p>MB Bank · 0898449950 · GÓC HOA XINH</p>
        <p>Hiển thị mã QR không xác nhận đã thanh toán. Cửa hàng sẽ kiểm tra chuyển khoản.</p>
    </div>
@endif
