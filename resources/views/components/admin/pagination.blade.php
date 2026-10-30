@props(['paginator', 'label' => 'mục'])
@once
    @push('styles')
        <link href="{{ asset('css/admin-pagination.css') }}" rel="stylesheet">
    @endpush
@endonce
<div class="admin-pagination" data-admin-pagination>
    <p class="admin-pagination-summary">
        Hiển thị <strong>{{ $paginator->firstItem() ?? 0 }} – {{ $paginator->lastItem() ?? 0 }}</strong>
        trong tổng <strong>{{ $paginator->total() }}</strong> {{ $label }}
    </p>
    {{ $paginator->onEachSide(1)->links('admin.partials.pagination') }}
    <form method="GET" action="{{ request()->url() }}" class="admin-pagination-size">
        @foreach(request()->except(['page', 'per_page']) as $key => $value)
            @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <label>Hiển thị
            <select name="per_page" class="form-control" aria-label="Số {{ $label }} mỗi trang" onchange="this.form.submit()">
                @foreach(\App\Support\AdminPagination::SIZES as $size)
                    <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </label>
        <span>{{ $label }}/trang</span>
        <noscript><button class="btn btn-primary btn-sm">Áp dụng</button></noscript>
    </form>
</div>
