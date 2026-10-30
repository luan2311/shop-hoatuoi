@props(['title'])
<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">{{ $title }}</h1></x-slot>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-4">
        @if(session('status'))<p role="status" class="p-3 bg-green-50">{{ session('status') }}</p>@endif
        @if($errors->any())
            <ul role="alert" class="p-3 bg-red-50 text-red-800">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif
        <div class="bg-white p-4 rounded shadow-sm space-y-4">{{ $slot }}</div>
    </div>
</x-app-layout>
