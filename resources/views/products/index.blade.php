@extends('layouts.app')

@section('title', ($currentCategory ? $currentCategory->name : ($keyword ? 'Tìm kiếm: ' . $keyword : 'Tất cả sản phẩm')) . ' - Shop Hoa Tươi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/collection_style.css') }}">
    <style>
        .sort-cate ul { margin: 0; padding: 0; list-style: none; }
        .sort-cate ul li.btn-quick-sort { display: inline-block; margin-right: 5px; }
        .sort-cate ul li.btn-quick-sort a {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            color: #495057;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .sort-cate ul li.btn-quick-sort.active a,
        .sort-cate ul li.btn-quick-sort a:hover {
            background-color: #537453;
            color: #ffffff;
            border-color: #537453;
        }
        .collection-paginate .pagination {
            justify-content: center;
            gap: 4px;
            margin-top: 20px;
        }
        .collection-paginate .page-link {
            color: #537453;
            border-radius: 4px;
            padding: 6px 12px;
        }
        .collection-paginate .page-item.active .page-link {
            background-color: #537453;
            border-color: #537453;
            color: #ffffff;
        }
    </style>
@endpush

@section('content')
<div class="layout-collection py-4">
    <div class="container">
        <div class="row">
            <div class="col-12 col-title mb-3">
                <h1 class="text-uppercase fw-bold" style="color: #537453; font-size: 2rem;">
                    @if(!empty($keyword))
                        Kết quả tìm kiếm cho: "{{ $keyword }}"
                    @elseif(!empty($currentCategory))
                        {{ $currentCategory->name }}
                    @else
                        Tất cả sản phẩm
                    @endif
                </h1>
                <p class="text-muted small">
                    Hiển thị {{ $products->count() }} trên tổng số {{ $products->total() }} sản phẩm
                </p>
            </div>

            <div class="block-collection col-lg-12 col-12">
                <div class="category-products products-view products-view-grid list_hover_pro">
                    <!-- Quick Sort Toolbar ported từ tat_ca_san_pham.cshtml -->
                    <div class="filter-containers mb-4">
                        <div class="sort-cate clearfix">
                            <div class="sort-cate-right d-flex align-items-center flex-wrap gap-2">
                                <h3 class="mb-0 d-flex align-items-center me-2 text-secondary" style="font-size: 14px; font-weight: 600;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-alpha-down me-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371h-1.781zm1.57-.785L11 2.687h-.047l-.652 2.157h1.351z"></path>
                                        <path d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645V14zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293V2.5z"></path>
                                    </svg> Xếp theo:
                                </h3>
                                @php
                                    $currentQuery = request()->except(['page', 'sort']);
                                @endphp
                                <ul class="d-flex flex-wrap list-unstyled mb-0 gap-1">
                                    <li class="btn-quick-sort default {{ ($currentSort === '' || $currentSort === 'default') ? 'active' : '' }}">
                                        <a href="{{ route('products.index', array_merge($currentQuery, ['sort' => 'default'])) }}" title="Mặc định">Mặc định</a>
                                    </li>
                                    <li class="btn-quick-sort alpha-asc {{ $currentSort === 'alpha-asc' ? 'active' : '' }}">
                                        <a href="{{ route('products.index', array_merge($currentQuery, ['sort' => 'alpha-asc'])) }}" title="Tên A-Z">Tên A-Z</a>
                                    </li>
                                    <li class="btn-quick-sort alpha-desc {{ $currentSort === 'alpha-desc' ? 'active' : '' }}">
                                        <a href="{{ route('products.index', array_merge($currentQuery, ['sort' => 'alpha-desc'])) }}" title="Tên Z-A">Tên Z-A</a>
                                    </li>
                                    <li class="btn-quick-sort price-asc {{ $currentSort === 'price-asc' ? 'active' : '' }}">
                                        <a href="{{ route('products.index', array_merge($currentQuery, ['sort' => 'price-asc'])) }}" title="Giá thấp đến cao">Giá thấp đến cao</a>
                                    </li>
                                    <li class="btn-quick-sort price-desc {{ $currentSort === 'price-desc' ? 'active' : '' }}">
                                        <a href="{{ route('products.index', array_merge($currentQuery, ['sort' => 'price-desc'])) }}" title="Giá cao xuống thấp">Giá cao xuống thấp</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="row">
                        @forelse ($products as $product)
                            @include('partials.product-card', ['product' => $product])
                        @empty
                            <div class="col-12 py-5 text-center">
                                <p class="text-muted fs-5">Không tìm thấy sản phẩm.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Phân trang Bootstrap ported từ tat_ca_san_pham.cshtml -->
                    @if ($products->hasPages())
                        <div class="pagenav mt-4">
                            <nav class="collection-paginate clearfix relative nav_pagi w_100">
                                <ul class="pagination clearfix">
                                    @if ($products->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">«</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">«</a></li>
                                    @endif

                                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                        @if ($page == $products->currentPage())
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                        @endif
                                    @endforeach

                                    @if ($products->hasMorePages())
                                        <li class="page-item"><a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">»</a></li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">»</span></li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
