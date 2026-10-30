<form action="{{ route('products.index') }}" method="get" data-product-search data-endpoint="{{ route('products.search') }}" class="relative mb-6 max-w-xl">
    <label for="product-search" class="block font-medium">Tìm sản phẩm</label>
    <input id="product-search" name="q" type="search" value="{{ request('q') }}" maxlength="100" autocomplete="off" aria-controls="product-search-results" class="w-full rounded border-gray-300">
    <button type="submit" class="mt-2 rounded bg-gray-800 px-4 py-2 text-white">Tìm kiếm</button>
    <div id="product-search-results" data-search-results aria-live="polite" class="absolute z-10 w-full rounded bg-white shadow" hidden></div>
</form>
<script src="{{ asset('js/search-realtime.js') }}" defer></script>
