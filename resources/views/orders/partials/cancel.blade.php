@if($order->canCustomerCancel())
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="document.getElementById('cancelOrder_{{ $order->id }}').showModal()">Hủy đơn hàng</button>
    @push('modals')
        <dialog id="cancelOrder_{{ $order->id }}" aria-labelledby="cancelTitle_{{ $order->id }}" style="width: min(420px, calc(100% - 24px)); border: 1px solid #ddd; border-radius: 12px; padding: 20px;">
            <h3 id="cancelTitle_{{ $order->id }}" class="h5">Hủy đơn hàng</h3>
            <p>Bạn có chắc chắn muốn hủy đơn hàng này không?</p>
            <form method="POST" action="{{ route('orders.cancel', $order->id) }}" onsubmit="this.querySelector('[type=submit]').disabled=true; this.querySelector('[type=submit]').textContent='Đang hủy...';">
                @csrf
                <label for="cancelReason_{{ $order->id }}">Lý do hủy (không bắt buộc)</label>
                <textarea id="cancelReason_{{ $order->id }}" name="reason" maxlength="200" class="form-control mb-3" rows="2"></textarea>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="this.closest('dialog').close()">Quay lại</button>
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </div>
            </form>
        </dialog>
    @endpush
@elseif($order->status === 'Pending' && $order->payment_method === 'Banking')
    <span class="small text-muted">Vui lòng liên hệ Admin để kiểm tra thanh toán và hủy đơn.</span>
@endif
