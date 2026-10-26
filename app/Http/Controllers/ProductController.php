<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductBrowseRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(ProductBrowseRequest $request)
    {
        $query = Product::query();
        if ($id = $request->validated('category_id')) {
            $category = Category::findOrFail($id);
            $query->where('category.code', $category->code);
        }
        if ($slug = $request->validated('category_slug')) {
            $category = Category::where('slug', $slug)->first();
            $query->where('category.code', $category?->code ?? '__missing_category__');
        }
        if (($keyword = trim($request->validated('q') ?? '')) !== '') {
            $query->matchingKeyword($keyword);
        }
        [$column, $direction] = match ($request->validated('sort')) {
            'price-asc' => ['price', 'asc'], 'price-desc' => ['price', 'desc'],
            'alpha-asc' => ['name', 'asc'], 'alpha-desc' => ['name', 'desc'],
            default => ['code', 'asc'],
        };
        $products = $query->orderBy($column, $direction)->orderBy('_id')->paginate(12)->withQueryString();

        return view('products.index', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $relatedProducts = Product::where('category.code', data_get($product->category, 'code'))
            ->where('_id', '!=', $product->getKey())->orderBy('code')->limit(4)->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function searchAjax(ProductBrowseRequest $request)
    {
        $keyword = trim($request->validated('q') ?? '');
        if ($keyword === '') {
            return response()->json(['products' => []]);
        }
        $products = Product::matchingKeyword($keyword)->orderBy('code')->limit(6)->get()
            ->map(fn (Product $product) => [
                'id' => (string) $product->getKey(), 'name' => $product->name,
                'price' => $product->price, 'formatted_price' => $product->formatted_price,
                'slug' => $product->slug, 'image_url' => $product->image_url,
                'url' => route('products.show', $product->slug),
            ]);

        return response()->json(['products' => $products]);
    }
}
