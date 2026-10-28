@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ SẢN PHẨM</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm sản phẩm..." />
        </div>
        <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
        @endif
    </form>
    <p class="text-right mb-0">
        <a href="{{ route('admin.products.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Tạo sản phẩm mới
        </a>
    </p>
</div>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th>Ảnh Sản Phẩm</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Giá Bán</th>
                    <th>Số lượng tồn kho</th>
                    <th>Tên loại hàng</th>
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
                                <span class="badge badge-danger mt-1">Ngưng bán</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-weight-bold text-dark">{{ number_format((float)$item->price, 0, ',', '.') }} VNĐ</span>
                        </td>
                        <td>
                            <span class="{{ $item->stock == 0 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $item->stock }}
                            </span>
                        </td>
                        <td>{{ data_get($item->category, 'name', 'Chưa phân loại') }}</td>
                        <td>
                            <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-primary btn-sm mb-1">
                                <i class="fas fa-edit"></i> Sửa
                            </a>

                            <form method="POST" action="{{ route('admin.products.stop-selling', $item) }}" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm mb-1"
                                    @if($item->stock == 0) disabled title="Sản phẩm đã ngưng bán" @else onclick="return confirm('Bạn có chắc chắn muốn ngưng bán sản phẩm này? Số lượng tồn sẽ được đặt về 0 và tình trạng sẽ chuyển thành Hết hàng.');" @endif>
                                    Ngưng bán
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.products.destroy', $item) }}" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm &quot;{{ $item->name }}&quot; không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm mb-1">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Không có sản phẩm nào phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Trang {{ $products->currentPage() }} / {{ $products->lastPage() }} (Tổng: {{ $products->total() }} sản phẩm)
    </div>
    <nav aria-label="Page navigation">
        {{ $products->links('pagination::bootstrap-4') }}
    </nav>
</div>
@endsection
