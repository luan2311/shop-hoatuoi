<x-sales-panel title="Thông tin đặt hàng">
    <div class="grid md:grid-cols-2 gap-6">
        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ old('checkout_token', $checkoutToken) }}">
            @foreach(['receiver_name' => ['Họ tên người nhận', auth()->user()->name], 'receiver_phone' => ['Số điện thoại', auth()->user()->phone], 'receiver_address' => ['Địa chỉ đầy đủ (số nhà, phường/xã, tỉnh/thành)', auth()->user()->address]] as $field => [$label, $value])
                <div><label class="block" for="{{ $field }}">{{ $label }}</label>
                <input class="w-full" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $value) }}" maxlength="{{ $field === 'receiver_address' ? 500 : ($field === 'receiver_phone' ? 11 : 100) }}" required @if($field === 'receiver_phone') type="tel" pattern="0[0-9]{9,10}" @endif></div>
            @endforeach
            <div><label class="block" for="email">Email</label><input class="w-full" type="email" id="email" name="email" maxlength="255" value="{{ old('email', auth()->user()->email) }}"></div>
            <div><label class="block" for="note">Ghi chú</label><textarea class="w-full" id="note" name="note" maxlength="1000">{{ old('note') }}</textarea></div>
            <div><label class="block" for="payment_method">Phương thức thanh toán</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="COD" @selected(old('payment_method') === 'COD')>Thanh toán khi nhận hàng</option>
                    <option value="Banking" @selected(old('payment_method') === 'Banking')>Chuyển khoản</option>
                </select>
                <p class="text-sm">Chọn chuyển khoản: cửa hàng sẽ liên hệ xác nhận thông tin thanh toán.</p>
            </div>
            <x-primary-button>Đặt hàng</x-primary-button>
        </form>
        <div>
            <h2 class="font-semibold">Tóm tắt đơn hàng</h2>
            @foreach($lines as $line)
                <p>{{ $line['product']?->name ?? 'Sản phẩm không còn tồn tại' }} × {{ $line['quantity'] }} — {{ number_format($line['subtotal'], 0, ',', '.') }} đ</p>
            @endforeach
            <p class="font-semibold">Tổng cộng: {{ number_format($total, 0, ',', '.') }} đ</p>
        </div>
    </div>
</x-sales-panel>
