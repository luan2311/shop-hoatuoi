<x-sales-panel title="Quản lý đơn hàng">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap gap-3">
        <label for="status">Trạng thái</label>
        <select id="status" name="status"><option value="">Tất cả</option>
            @foreach(\App\Models\Order::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
        </select>
        <label for="q">Mã đơn hoặc người nhận</label><input id="q" name="q" value="{{ request('q') }}" maxlength="100">
        <button class="underline">Lọc</button>
    </form>
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-left">
        <thead><tr><th>Mã đơn</th><th>Người nhận</th><th>Trạng thái</th><th>Tổng tiền</th></tr></thead>
        <tbody>@forelse($orders as $order)
            <tr class="border-t"><td class="p-2"><a class="underline" href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_code }}</a></td><td class="p-2">{{ $order->customer['name'] ?? '—' }}</td><td class="p-2">{{ $order->status }}</td><td class="p-2">{{ number_format((float) (string) $order->total_amount, 0, ',', '.') }} đ</td></tr>
        @empty<tr><td colspan="4">Chưa có đơn hàng phù hợp.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $orders->links() }}
</x-sales-panel>
