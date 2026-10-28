@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<h1 class="mb-4 text-center font-weight-bold" style="color: #333;">QUẢN LÝ SẢN PHẨM</h1>

<div class="mb-3 d-flex justify-content-between align-items-center">
    <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm sản phẩm..." />
        </div>
        <button type="submit" class="ml-2 btn btn-primary">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
        <a href="{{ route('admin.products.index') }}" class="ml-2 btn btn-outline-secondary">Đặt lại</a>
        @endif
    </form>
    <p class="mb-0 text-right">
        <a href="{{ route('admin.products.create') }}" class="btn btn-success">
            <i class="mr-1 fas fa-plus"></i> Tạo sản phẩm mới
        </a>
    </p>
</div>

<div class="p-0 border-0 shadow-sm table-container" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table mb-0 table-light table-striped table-hover">
            <thead>
                <tr class="thead-light">
                    <th>Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Giá Bán</th>
                    <th style="width: 90px;">Tồn kho</th>
                    <th>Phân loại</th>
                    <th style="min-width: 180px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $item)
                <tr @if($item->stock == 0) style="background-color: #ffebee; opacity: 0.7;" @endif>
                    <td>
                        @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-width: 90px; max-height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;" />
                        @else
                        <span class="badge badge-secondary">Không có ảnh</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        <div class="small text-muted">Mã: {{ $item->code }}</div>
                        @if ($item->stock == 0)
                        <span class="mt-1 badge badge-danger">Ngưng bán</span>
                        @endif
                    </td>
                    <td>
                        <span class=" text-dark">{{ number_format((float)$item->price, 0, ',', '.') }} VNĐ</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="{{ $item->stock == 0 ? 'text-danger font-weight-bold' : '' }}">
                            {{ $item->stock }}
                        </span>
                    </td>
                    <td>{{ data_get($item->category, 'name', 'Chưa phân loại') }}</td>
                    <td>
                        <a href="{{ route('admin.products.edit', $item) }}" class="mb-1 btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>

                        <form method="POST" action="{{ route('admin.products.stop-selling', $item) }}" style="display: inline-block;">
                            @csrf
                            <button type="submit" class="mb-1 btn btn-warning btn-sm"
                                @if($item->stock == 0) disabled title="Sản phẩm đã ngưng bán" @else onclick="return confirm('Bạn có chắc chắn muốn ngưng bán sản phẩm này? Số lượng tồn sẽ được đặt về 0 và tình trạng sẽ chuyển thành Hết hàng.');" @endif>
                                <i class="mr-1 fas fa-ban"></i> Ngưng bán
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-muted">Không có sản phẩm nào phù hợp.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between align-items-center">
    <div class="text-muted">
        Trang {{ $products->currentPage() }} / {{ $products->lastPage() }} (Tổng: {{ $products->total() }} sản phẩm)
    </div>
    <nav aria-label="Page navigation">
        {{ $products->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection