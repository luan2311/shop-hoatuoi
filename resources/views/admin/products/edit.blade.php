<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Sửa sản phẩm</h1></x-slot>
    <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mx-auto max-w-4xl p-4 sm:p-6">
        @method('PUT')
        @include('admin.products.partials.form')
    </form>
</x-app-layout>
