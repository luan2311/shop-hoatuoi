<x-app-layout>
    <x-slot name="header"><h1 class="break-words text-xl font-semibold">{{ $product->name }}</h1></x-slot>
    <div class="mx-auto max-w-7xl p-4 sm:p-6">
        <a href="{{ route('products.index') }}" class="underline">Danh sách sản phẩm</a>
        <div class="mt-4 grid gap-6 md:grid-cols-2">
            <div>@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full rounded">@else<p>Chưa có ảnh</p>@endif</div>
            <div class="min-w-0 space-y-3">
                <p>Mã: {{ $product->code }}</p>
                <p>{{ data_get($product->category, 'name') }}</p>
                <p class="text-xl font-semibold">{{ $product->formatted_price }}</p>
                <p>{{ $product->status }} · Tồn kho: {{ $product->stock }}</p>
                <p>Thương hiệu: {{ $product->brand }}</p>
                <div class="whitespace-pre-line break-words">{{ strip_tags($product->description ?? '') }}</div>
            </div>
        </div>
        <h2 class="my-6 text-lg font-semibold">Sản phẩm cùng danh mục</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($relatedProducts as $item)@include('products.partials.card')@endforeach
        </div>
    </div>
</x-app-layout>
