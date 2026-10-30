<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductBrowseRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Danh sách sản phẩm (hỗ trợ phân trang 12 sp/trang, lọc danh mục, tìm kiếm và sắp xếp)
     * Port từ C# MVC tat_ca_san_pham / bo_hoa_tuoi / ... trong SanPhamController.cs
     */
    public function index(ProductBrowseRequest $request)
    {
        $query = Product::selling();
        $currentCategory = null;

        if ($id = $request->validated('category_id')) {
            $currentCategory = Category::findOrFail($id);
            $query->where('category.code', $currentCategory->code);
        }

        if ($slug = $request->validated('category_slug')) {
            $currentCategory = Category::where('slug', $slug)->first();
            $query->where('category.code', $currentCategory?->code ?? '__missing_category__');
        }

        $keyword = trim($request->validated('q') ?? '');
        if ($keyword !== '') {
            $query->matchingKeyword($keyword);
        }

        $sort = $request->validated('sort');
        [$column, $direction] = match ($sort) {
            'price-asc' => ['price', 'asc'],
            'price-desc' => ['price', 'desc'],
            'alpha-asc' => ['name', 'asc'],
            'alpha-desc' => ['name', 'desc'],
            default => ['code', 'asc'],
        };

        $products = $query->orderBy($column, $direction)->orderBy('_id')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'currentSort' => $sort ?? 'default',
            'keyword' => $keyword,
        ]);
    }

    /**
     * Chi tiết sản phẩm & 4 sản phẩm liên quan
     * Port từ C# MVC chi_tiet_san_pham(string id) trong SanPhamController.cs
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->orWhere('code', $slug)
            ->firstOrFail();

        $relatedProducts = Product::selling()->where('category.code', data_get($product->category, 'code'))
            ->where('_id', '!=', $product->getKey())
            ->orderBy('code')
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Tìm kiếm realtime qua AJAX (gợi ý dropdown ở thanh search header)
     * Port từ C# MVC SearchAjax(string keyword) trong SanPhamController.cs
     */
    public function searchAjax(ProductBrowseRequest $request)
    {
        $keyword = trim($request->validated('q') ?? '');
        if ($keyword === '') {
            return response()->json(['products' => []]);
        }

        $products = Product::selling()->matchingKeyword($keyword)->orderBy('code')->limit(6)->get()
            ->map(fn (Product $product) => [
                'id' => (string) $product->getKey(),
                'code' => $product->code,
                'name' => $product->name,
                'price' => $product->price,
                'formatted_price' => $product->formatted_price,
                'slug' => $product->slug,
                'image_url' => $product->image_url ?? asset('storage/products/'.$product->image),
                'url' => route('products.show', $product->slug),
            ]);

        return response()->json(['products' => $products]);
    }

    /**
     * Lấy thông tin sản phẩm dạng JSON qua AJAX (dùng cho modal xem nhanh / add to cart)
     * Port từ C# MVC GetProductInfo(string id) trong SanPhamController.cs
     */
    public function getProductInfo(string $id)
    {
        $product = Product::where('code', $id)
            ->orWhere('_id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => (string) $product->getKey(),
                'MaSP' => $product->code,
                'TenSP' => $product->name,
                'GiaBan' => $product->price,
                'formatted_price' => $product->formatted_price,
                'AnhSP' => $product->image,
                'image_url' => $product->image_url ?? asset('storage/products/'.$product->image),
                'MoTaSP' => $product->description,
                'SoLuongTon' => $product->stock,
                'TinhTrang' => $product->status,
                'ThuongHieu' => $product->brand,
                'TenLoai' => data_get($product->category, 'name'),
                'Slug' => $product->slug,
            ],
        ]);
    }

    /**
     * RESTful API: Lấy toàn bộ danh sách sản phẩm
     * Port từ C# Web API GetAllSanPham trong APIController/SanPhamController.cs
     */
    public function apiIndex()
    {
        $products = Product::orderBy('code')->get()->map(fn (Product $p) => [
            'id' => (string) $p->getKey(),
            'code' => $p->code,
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => $p->price,
            'formatted_price' => $p->formatted_price,
            'image' => $p->image,
            'image_url' => $p->image_url ?? asset('storage/products/'.$p->image),
            'stock' => $p->stock,
            'status' => $p->status,
            'brand' => $p->brand,
            'category' => $p->category,
        ]);

        return response()->json($products);
    }

    /**
     * RESTful API: Lấy chi tiết sản phẩm theo mã SP hoặc ID
     * Port từ C# Web API GetSanPhamById(string id) trong APIController/SanPhamController.cs
     */
    public function apiShow(string $id)
    {
        $product = Product::where('code', $id)
            ->orWhere('_id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        return response()->json([
            'id' => (string) $product->getKey(),
            'code' => $product->code,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'formatted_price' => $product->formatted_price,
            'image' => $product->image,
            'image_url' => $product->image_url ?? asset('storage/products/'.$product->image),
            'description' => $product->description,
            'stock' => $product->stock,
            'status' => $product->status,
            'brand' => $product->brand,
            'category' => $product->category,
        ]);
    }
}
