<x-sales-panel title="Đơn hàng của tôi">
    @forelse($orders as $order)
        <p><a class="underline" href="{{ route('orders.show', $order->id) }}">{{ $order->order_code }}</a> — {{ $order->status }} — {{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ</p>
    @empty
        <p>Bạn chưa có đơn hàng.</p>
    @endforelse
    {{ $orders->links() }}
</x-sales-panel>
