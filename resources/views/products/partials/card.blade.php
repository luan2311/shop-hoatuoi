<article class="rounded bg-white p-4 shadow">
    <a href="{{ route('products.show', $item->slug) }}" class="block">
        @if($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy" class="h-48 w-full rounded object-cover">
        @else
            <div class="flex h-48 items-center justify-center rounded bg-gray-100 text-gray-500">Chưa có ảnh</div>
        @endif
        <h2 class="mt-3 break-words font-semibold">{{ $item->name }}</h2>
    </a>
    <p>{{ $item->formatted_price }}</p>
    <p class="text-sm text-gray-600">{{ $item->status }}</p>
</article>
