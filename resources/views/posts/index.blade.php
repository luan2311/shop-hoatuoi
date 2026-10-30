@extends('layouts.app')

@section('title', 'Tin tức')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/blog_style.css') }}">
@endpush

@section('content')
    @php
        // Trang 1: 1 bài lớn + 3 bài nhỏ ở trên, phần còn lại xuống lưới bên dưới
        $isFirstPage = $posts->onFirstPage();
        $tinTucDauTien = $isFirstPage ? $posts->first() : null;
        $tinTucConLai = $isFirstPage ? $posts->slice(1, 3) : collect();
        $danhSach = $isFirstPage ? $posts->slice(4) : $posts->getCollection();
    @endphp

    <div class="blog_wrapper layout-blog">
        <div class="container">
            <div class="row">
                <div class="right-content col-lg-9 col-12">
                    <h1 class="title-page">Tin tức</h1>

                    @if ($posts->isEmpty())
                        <p class="text-muted" style="font-size: 1.4rem;">Chưa có bài viết nào.</p>
                    @endif

                    @if ($tinTucDauTien)
                        <div class="top-new-page">
                            <div class="row">
                                <div class="top-news-left col-12 col-md-6 col-lg-7">
                                    <div class="new-item">
                                        <a class="image-blog" href="/posts/{{ $tinTucDauTien->slug }}" title="{{ $tinTucDauTien->title }}">
                                            <img class="img_blog duration-300 lazyload loaded" src="{{ $tinTucDauTien->thumbnail_url }}" alt="{{ $tinTucDauTien->title }}" data-was-processed="true">
                                        </a>
                                        <div class="content-box">
                                            <p class="time-post">
                                                @include('posts._clock')
                                                <span>{{ $tinTucDauTien->display_date }}</span>
                                            </p>
                                            <h3><a title="{{ $tinTucDauTien->title }}" href="/posts/{{ $tinTucDauTien->slug }}">{{ $tinTucDauTien->title }}</a></h3>
                                            <p class="justify line-clamp line-clamp-3" style="display:none">
                                                {{ $tinTucDauTien->excerpt }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="top-news-right col-12 col-md-6 col-lg-5">
                                    @foreach ($tinTucConLai as $item)
                                        <div class="new-item-right">
                                            <div class="thumb">
                                                <a class="image-blog" href="/posts/{{ $item->slug }}" title="{{ $item->title }}">
                                                    <img class="img_blog lazyload loaded" src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" data-was-processed="true">
                                                </a>
                                            </div>
                                            <div class="contentright">
                                                <h3 class="line-clamp line-clamp-2"><a title="{{ $item->title }}" href="/posts/{{ $item->slug }}">{{ $item->title }}</a></h3>
                                                <p class="time-post">
                                                    @include('posts._clock')
                                                    <span>{{ $item->display_date }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row list-news">
                        @foreach ($danhSach as $item)
                            <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                                <div class="item-blog">
                                    <a class="block-thumb thumb" href="/posts/{{ $item->slug }}" title="{{ $item->title }}">
                                        <img width="400" height="240" class="lazyload duration-300 loaded" src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}">
                                    </a>
                                    <div class="block-content">
                                        <h3>
                                            <a href="/posts/{{ $item->slug }}" title="{{ $item->title }}" class="line-clamp line-clamp-2">{{ $item->title }}</a>
                                        </h3>
                                        <p class="justify line-clamp line-clamp-3">{{ $item->excerpt }}</p>
                                        <p class="time-post">
                                            @include('posts._clock')
                                            <span>{{ $item->display_date }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="pagi-div">
                            {{ $posts->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                @include('posts._sidebar')
            </div>
        </div>
    </div>
@endsection
