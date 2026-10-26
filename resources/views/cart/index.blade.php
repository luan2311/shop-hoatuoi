<x-sales-panel title="Giỏ hàng">
    @if(empty($lines))
        <p>Giỏ hàng đang trống.</p>
    @else
        <div class="relative overflow-x-auto">
            <table class="w-full min-w-[640px] text-left">
                <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th><th>Thao tác</th></tr></thead>
                <tbody>
                @foreach($lines as $line)
                    <tr class="border-t">
                        <td class="p-2">
                            @if($line['product']?->image)
                                <img class="w-16 h-16 object-cover" src="{{ asset('storage/products/'.basename($line['product']->image)) }}" alt="">
                            @endif
                            {{ $line['product']?->name ?? $line['code'].' (không còn tồn tại)' }}
                        </td>
                        <td class="p-2">{{ $line['unit_price'] !== null ? number_format($line['unit_price'], 0, ',', '.').' đ' : '—' }}</td>
                        <td class="p-2">
                            <form method="POST" action="{{ route('cart.update', $line['code']) }}" class="flex flex-wrap gap-2">
                                @csrf @method('PATCH')
                                <label class="sr-only" for="quantity-{{ $loop->index }}">Số lượng {{ $line['code'] }}</label>
                                <button type="button" onclick="this.form.querySelector('input[name=quantity]').stepDown()" aria-label="Giảm số lượng">−</button>
                                <input class="w-20" id="quantity-{{ $loop->index }}" name="quantity" type="number" min="1" max="999" value="{{ $line['quantity'] }}" required>
                                <button type="button" onclick="this.form.querySelector('input[name=quantity]').stepUp()" aria-label="Tăng số lượng">+</button>
                                <button class="underline" type="submit">Cập nhật</button>
                            </form>
                        </td>
                        <td class="p-2">{{ number_format($line['subtotal'], 0, ',', '.') }} đ</td>
                        <td class="p-2"><form method="POST" action="{{ route('cart.remove', $line['code']) }}">@csrf @method('DELETE')<button class="underline">Xóa</button></form></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="font-semibold">Tạm tính: {{ number_format($total, 0, ',', '.') }} đ</p>
        <div class="flex flex-wrap gap-4">
            <a class="underline" href="{{ route('checkout') }}">Tiến hành thanh toán</a>
            <form method="POST" action="{{ route('cart.clear') }}">@csrf @method('DELETE')<button class="underline">Làm trống giỏ hàng</button></form>
        </div>
    @endif
</x-sales-panel>
