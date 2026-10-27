<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Quản lý sản phẩm</h1></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-4 sm:p-6">
        @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
        <a class="inline-block rounded bg-gray-800 px-4 py-2 text-white" href="{{ route('admin.products.create') }}">Thêm sản phẩm</a>
        <form method="get"><label>Tìm kiếm <input name="q" value="{{ request('q') }}" maxlength="100" class="rounded border-gray-300"></label><button class="px-3 underline">Tìm</button></form>
        <div class="overflow-x-auto bg-white">
            <table class="w-full text-left">
                <thead><tr><th class="p-3">Ảnh</th><th>Mã / Tên</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Thao tác</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-t">
                            <td class="p-3">@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-16 w-16 object-cover">@endif</td>
                            <td>{{ $product->code }} / {{ $product->name }}</td><td>{{ data_get($product->category, 'name') }}</td>
                            <td>{{ $product->formatted_price }}</td><td>{{ $product->stock }} · {{ $product->status }}</td>
                            <td class="p-3"><a class="underline" href="{{ route('admin.products.edit', $product) }}">Sửa</a>
                                <form method="post" action="{{ route('admin.products.destroy', $product) }}">@csrf @method('DELETE')<button class="text-red-700">Xóa</button></form>
                            </td>
                        </tr>
                    @empty<tr><td colspan="6" class="p-3">Không có sản phẩm.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </div>
</x-app-layout>
