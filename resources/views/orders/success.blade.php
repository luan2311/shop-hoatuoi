<x-sales-panel title="Đặt hàng thành công">
    <p role="status">Cửa hàng đã nhận đơn hàng của bạn.</p>
    @include('orders.partials.detail')
    <a class="underline" href="{{ route('orders.index') }}">Xem lịch sử đơn hàng</a>
</x-sales-panel>
