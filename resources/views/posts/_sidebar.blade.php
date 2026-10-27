{{-- Cột "Tin tức nổi bật" dùng chung cho danh sách và chi tiết (Tin_tuc.cshtml / Details.cshtml cũ) --}}
<div class="blog_left_base col-lg-3 col-12" style="padding-top:30px">
    <div class="side-right-stick">
        <div class="blog_noibat">
            <h2 class="h2_sidebar_blog">
                <a href="/posts" title="Tin tức nổi bật">Tin tức nổi bật</a>
            </h2>
            <div class="blog_content">
                @foreach ($tinTucNoiBat as $item)
                    <div class="item clearfix">
                        <div class="post-thumb">
                            <a class="image-blog scale_hover" href="/posts/{{ $item->slug }}" title="{{ $item->title }}" style=" opacity: inherit;">
                                <img class="img_blog lazyload loaded" src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" data-was-processed="true">
                            </a>
                        </div>
                        <div class="contentright">
                            <h3><a title="{{ $item->title }}" href="/posts/{{ $item->slug }}">{{ $item->title }}</a></h3>
                            <p class="time-post">
                                <span>{{ $item->display_date }}</span>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
