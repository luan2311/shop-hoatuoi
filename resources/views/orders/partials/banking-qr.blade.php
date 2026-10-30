@if($order->payment_method === 'Banking' && $order->status !== 'Cancelled')
    <div class="banking-info-box" style="margin-top: 20px; padding: 20px; background: #fdfbf7; border: 1.5px solid #d4e2d1; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
        <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
            <div style="text-align: center; flex-shrink: 0; margin: 0 auto;">
                <img src="https://api.vietqr.io/image/970422-0898449950-compact2.jpg?{{ http_build_query(['amount' => (int) (string) $order->total_amount, 'addInfo' => $order->order_code, 'accountName' => 'TIEM HOA GOC HOA XINH'], '', '&', PHP_QUERY_RFC3986) }}"
                     alt="VietQR Mã Thanh Toán" class="banking-qr-code" style="max-width: 220px; width: 100%; height: auto; border-radius: 10px; border: 1px solid #d4e2d1; box-shadow: 0 4px 12px rgba(83,117,71,0.15); display: block;">
                <div style="font-size: 12px; color: #16a34a; font-weight: 600; margin-top: 8px;">
                    <i class="fa-solid fa-qrcode"></i> Quét mã để tự động điền số tiền
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; font-size: 14px;">
                <div style="font-size: 16px; font-weight: 700; color: #1a2f20; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-building-columns text-success"></i> Thông tin chuyển khoản thanh toán
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: #64748b;">Ngân hàng:</span>
                    <strong style="color: #1a2f20;">MB Bank (Ngân Hàng Quân Đội)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: #64748b;">Số tài khoản:</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <strong style="color: #1a2f20; letter-spacing: 0.5px; font-size: 15px;">0898449950</strong>
                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 px-2" style="font-size: 11px; border-radius: 4px;" onclick="navigator.clipboard.writeText('0898449950'); this.innerHTML='<i class=\'fa fa-check text-success\'></i> Đã chép'; setTimeout(()=>this.innerHTML='<i class=\'fa fa-copy\'></i> Chép', 2000);" title="Sao chép số tài khoản">
                            <i class="fa fa-copy"></i> Chép
                        </button>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: #64748b;">Chủ tài khoản:</span>
                    <strong style="color: #1a2f20;">GÓC HOA XINH</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: #64748b;">Số tiền cần chuyển:</span>
                    <strong style="color: #b91c1c; font-size: 16px;">{{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: #64748b;">Cú pháp chuyển khoản:</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <strong style="font-family: monospace; background: #eef2f6; padding: 2px 8px; border-radius: 4px; color: #0284c7; font-size: 14px;">{{ $order->order_code }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 px-2" style="font-size: 11px; border-radius: 4px;" onclick="navigator.clipboard.writeText('{{ $order->order_code }}'); this.innerHTML='<i class=\'fa fa-check text-success\'></i> Đã chép'; setTimeout(()=>this.innerHTML='<i class=\'fa fa-copy\'></i> Chép', 2000);" title="Sao chép cú pháp chuyển khoản">
                            <i class="fa fa-copy"></i> Chép
                        </button>
                    </div>
                </div>

                <div style="margin-top: 12px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 12.5px; color: #166534; line-height: 1.45;">
                    <i class="fa-solid fa-circle-check text-success"></i> <strong>Tự động điền:</strong> Khi quét mã QR bằng App Ngân hàng hoặc Ví MoMo, số tiền <strong>{{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ</strong> và mã đơn <strong>{{ $order->order_code }}</strong> sẽ tự động được điền chính xác. Cửa hàng sẽ kiểm tra và xác nhận chuyển khoản để giao hoa sớm nhất!
                </div>
            </div>
        </div>
    </div>
@endif
