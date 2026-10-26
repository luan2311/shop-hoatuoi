@extends('layouts.app')

@section('title', $post->title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/blog_style.css') }}">
@endpush

@section('content')
    <section class="blogpage">
        <div class="container layout-article" itemscope="" itemtype="https://schema.org/Article">
            <div class="bg_blog">
                <article class="article-main">
                    <div class="row">
                        <div class="right-content col-lg-9 col-12">
                            <div class="article-details clearfix">
                                <h1 class="article-title">{{ $post->title }}</h1>
                                <div class="posts">
                                    <div class="time-post f">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="clock" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-clock fa-w-16"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm216 248c0 118.7-96.1 216-216 216-118.7 0-216-96.1-216-216 0-118.7 96.1-216 216-216 118.7 0 216 96.1 216 216zm-148.9 88.3l-81.2-59c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h14c6.6 0 12 5.4 12 12v146.3l70.5 51.3c5.4 3.9 6.5 11.4 2.6 16.8l-8.2 11.3c-3.9 5.3-11.4 6.5-16.8 2.6z" class=""></path></svg>

                                        {{ $post->display_date }}
                                    </div>
                                </div>
                                <div class="article-image">
                                    <img class="lazyload loaded" src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" data-was-processed="true">
                                </div>
                                {{-- Nội dung bài viết là HTML do admin/seeder nhập (như @Html.Raw của đồ án cũ) --}}
                                {!! $post->content !!}
                            </div>

                            @if ($tinTucLienQuan->isNotEmpty())
                                <div class="blog_related">
                                    <div class="title">Tin tức liên quan</div>
                                    <div class="row">
                                        @foreach ($tinTucLienQuan as $item)
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <div class="item-blog">
                                                    <a class="block-thumb thumb" href="/posts/{{ $item->slug }}" title="{{ $item->title }}">
                                                        <img width="400" height="240" class="lazyload duration-300" src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}">
                                                    </a>
                                                    <div class="block-content">
                                                        <h3>
                                                            <a href="/posts/{{ $item->slug }}" title="{{ $item->title }}" class="line-clamp line-clamp-2">{{ $item->title }}</a>
                                                        </h3>
                                                        <p class="justify line-clamp line-clamp-3">{{ $item->excerpt }}</p>
                                                        <p class="time-post">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-fill" viewBox="0 0 16 16">
                                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"></path>
                                                            </svg>
                                                            <span>{{ $item->display_date }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @include('posts._sidebar')
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection
