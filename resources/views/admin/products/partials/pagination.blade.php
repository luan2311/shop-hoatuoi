@if($paginator->hasPages())
<nav aria-label="Phân trang sản phẩm">
    <ul class="pagination pagination-sm mb-0">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">@if($paginator->onFirstPage())<span class="page-link" aria-disabled="true">« Trước</span>@else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">« Trước</a>@endif</li>
        @foreach($elements as $element)
            @if(is_string($element))<li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>@endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">@if($page == $paginator->currentPage())<span class="page-link" aria-current="page">{{ $page }}</span>@else<a class="page-link" href="{{ $url }}" aria-label="Trang {{ $page }}">{{ $page }}</a>@endif</li>
                @endforeach
            @endif
        @endforeach
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">@if($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau »</a>@else<span class="page-link" aria-disabled="true">Sau »</span>@endif</li>
    </ul>
</nav>
@endif
