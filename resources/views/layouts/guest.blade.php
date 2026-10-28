@extends('layouts.app')

@section('title', $title ?? 'Tài khoản - Shop Hoa Tươi Góc Hoa Xinh')

@section('content')
    @isset($slot)
        {{ $slot }}
    @else
        @yield('guest_content')
    @endisset
@endsection
