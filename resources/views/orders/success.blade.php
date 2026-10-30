<x-sales-panel title="Đặt hàng thành công">
    <div class="py-4">
        <!-- Header Celebration Card -->
        <div class="text-center mb-5 p-4 rounded-3" style="background: linear-gradient(135deg, #f4f8f3 0%, #e8f0e6 100%); border: 1px solid #d4e2d1;">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; background: #537547; color: #ffffff; font-size: 32px; box-shadow: 0 4px 15px rgba(83, 117, 71, 0.3);">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 class="h3 fw-bold mb-2" style="font-family: 'Playfair Display', Georgia, serif; color: #1a2f20;">
                Đặt hàng thành công!
            </h2>
            <p role="status" class="mb-3 text-muted" style="font-size: 15px;">
                Cửa hàng đã nhận đơn hàng của bạn và đang chuẩn bị những bông hoa tươi đẹp nhất.
            </p>
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: #ffffff; border: 1px solid #c8d3c5; font-size: 13.5px; color: #3b5f43;">
                <i class="fa-solid fa-clock text-success"></i> Dự kiến giao nhanh trong <strong>2 giờ</strong> hoặc theo thời gian quý khách yêu cầu.
            </div>
        </div>

        <!-- Order Information Details Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff; border: 1px solid #e8ede6 !important;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold" style="color: #1a2f20; font-size: 15px;">
                    <i class="fa-solid fa-receipt text-success me-2"></i> Chi tiết đơn hàng
                </span>
                <span class="badge" style="background: #eef4ec; color: #3b5f43; font-size: 12.5px; font-weight: 600;">
                    Mã đơn: {{ $order->order_code }}
                </span>
            </div>
            <div class="card-body p-4">
                @include('orders.partials.detail')
            </div>
        </div>

        <p><a href="{{ route('orders.show', $order->id) }}">Xem chi tiết và tùy chọn hủy đơn hàng</a></p>
        <!-- Action Navigation Buttons -->
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center pt-2">
            <a href="{{ route('home') }}" class="btn fw-semibold d-inline-flex align-items-center justify-content-center text-center gap-2" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; padding: 10px 24px; min-height: 44px; height: auto; line-height: 1.5; text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Tiếp tục mua hoa</span>
            </a>
            <a href="{{ route('orders.index') }}" class="btn text-white fw-semibold d-inline-flex align-items-center justify-content-center text-center gap-2" style="background: #537547; color: #ffffff !important; border: 1px solid #537547; border-radius: 8px; box-shadow: 0 4px 12px rgba(83, 117, 71, 0.25); font-size: 15px; padding: 10px 24px; min-height: 44px; height: auto; line-height: 1.5; text-decoration: none;">
                <i class="fa-solid fa-box-archive"></i>
                <span>Quản lý lịch sử đơn hàng</span>
            </a>
        </div>
    </div>
</x-sales-panel>
