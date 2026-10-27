<x-sales-panel title="Quản trị chi tiết đơn hàng">
    @include('orders.partials.detail')
    @if($next = \App\Models\Order::TRANSITIONS[$order->status] ?? [])
        <form method="POST" action="{{ route('admin.orders.status', $order->id) }}" class="flex flex-wrap gap-3">
            @csrf @method('PATCH')
            <label for="status">Chuyển trạng thái</label>
            <select id="status" name="status">@foreach($next as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select>
            <button class="underline">Cập nhật</button>
        </form>
    @endif
    <a class="underline" href="{{ route('admin.orders.index') }}">Về danh sách</a>
</x-sales-panel>
