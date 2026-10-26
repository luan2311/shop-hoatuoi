<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Sản phẩm</h1></x-slot>
    <div class="mx-auto max-w-7xl p-4 sm:p-6">
        @if($errors->any())<p role="alert" class="mb-4 text-red-700">{{ $errors->first() }}</p>@endif
        <form method="get" action="{{ route('products.index') }}" class="mb-6 flex flex-wrap gap-3">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <label>Danh mục
                <select name="category_slug" class="max-w-full rounded border-gray-300">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category_slug') === $category->slug || request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Sắp xếp
                <select name="sort" class="rounded border-gray-300">
                    <option value="">Mặc định</option>
                    @foreach(['price-asc' => 'Giá tăng dần', 'price-desc' => 'Giá giảm dần', 'alpha-asc' => 'Tên A–Z', 'alpha-desc' => 'Tên Z–A'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded bg-gray-800 px-4 py-2 text-white">Lọc</button>
        </form>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($products as $item)
                @include('products.partials.card')
            @empty
                <p>Không tìm thấy sản phẩm.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</x-app-layout>
