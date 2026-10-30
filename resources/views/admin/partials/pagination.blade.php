<nav aria-label="Phân trang" class="admin-pagination-nav">
    <ul class="pagination mb-0">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if($paginator->onFirstPage())
                <span class="page-link" aria-disabled="true">‹ <span>Trước</span></span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">‹ <span>Trước</span></a>
            @endif
        </li>
        @foreach($elements as $element)
            @if(is_string($element))<li class="page-item disabled"><span class="page-link">…</span></li>@endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                        @if($page == $paginator->currentPage())
                            <span class="page-link" aria-current="page" aria-label="Trang {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}" aria-label="Trang {{ $page }}">{{ $page }}</a>
                        @endif
                    </li>
                @endforeach
            @endif
        @endforeach
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau"><span>Sau</span> ›</a>
            @else
                <span class="page-link" aria-disabled="true"><span>Sau</span> ›</span>
            @endif
        </li>
    </ul>
</nav>
