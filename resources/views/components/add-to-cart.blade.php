@props(['product'])
<form method="POST" action="{{ route('cart.add') }}" class="relative flex flex-wrap gap-2">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <label class="sr-only" for="add-quantity-{{ $product->id }}">Số lượng {{ $product->name }}</label>
    <input class="w-20" id="add-quantity-{{ $product->id }}" type="number" name="quantity" value="1" min="1" max="{{ min(999, max(1, (int) $product->stock)) }}" required>
    <x-primary-button :disabled="$product->status !== 'Còn hàng' || $product->stock < 1">Thêm vào giỏ</x-primary-button>
</form>
