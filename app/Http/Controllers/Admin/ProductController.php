<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductBrowseRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductLifecycle;
use App\Support\AdminPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;
use Throwable;

class ProductController extends Controller
{
    public function index(AdminProductBrowseRequest $request)
    {
        $perPage = AdminPagination::perPage($request);
        $query = Product::with('categoryRecord');
        if (($keyword = trim($request->validated('q') ?? '')) !== '') {
            $literal = new Regex(preg_quote($keyword), 'i');
            $query->where(fn ($q) => $q->matchingKeyword($keyword)->orWhere('code', 'regex', $literal));
        }

        $categories = Category::orderBy('name')->get();
        $category = null;
        if ($id = $request->validated('category_id')) {
            $category = $categories->first(fn ($item) => (string) $item->id === $id);
        } elseif ($slug = $request->validated('category_slug')) {
            $category = $categories->firstWhere('slug', $slug);
        }
        if ($request->filled('category_id') || $request->filled('category_slug')) {
            $query->where(function ($q) use ($category) {
                $q->where('category.code', $category?->code ?? '__missing_category__');
                if ($category) {
                    $q->orWhere(fn ($legacy) => $legacy->whereNull('category.code')
                        ->whereIn('category_id', [(string) $category->id, new ObjectId((string) $category->id)]));
                }
            });
        }
        match ($request->validated('sale_state')) {
            'selling' => $query->selling(),
            'stopped' => $query->whereNotNull('is_selling')->where('is_selling', '!=', true),
            'out-of-stock' => $query->where('stock', 0),
            default => null,
        };
        [$column, $direction] = match ($request->validated('sort')) {
            'price-asc' => ['price', 'asc'], 'price-desc' => ['price', 'desc'],
            'alpha-asc', 'default' => ['name', 'asc'], 'alpha-desc' => ['name', 'desc'],
            'stock-asc' => ['stock', 'asc'], 'stock-desc' => ['stock', 'desc'],
            default => ['created_at', 'desc'],
        };
        $products = $query->orderBy($column, $direction)->orderBy('_id', $direction)->paginate($perPage)->withQueryString();
        $total = Product::count();
        $selling = Product::selling()->count();
        $stats = ['total' => $total, 'selling' => $selling, 'stopped' => $total - $selling, 'out-of-stock' => Product::where('stock', 0)->count()];
        $deleteReasons = [];
        foreach ($products as $product) {
            try {
                $deleteReasons[(string) $product->id] = app(ProductLifecycle::class)->deletionReason($product);
            } catch (Throwable) {
                $deleteReasons[(string) $product->id] = 'Chưa xác minh được điều kiện xóa an toàn.';
            }
        }

        return view('admin.products.index', compact('products', 'categories', 'stats', 'deleteReasons'));
    }

    public function create()
    {
        return view('admin.products.create', ['product' => new Product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request)
    {
        $this->persist($request, new Product);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo sản phẩm thành công.',
                'redirect' => route('admin.products.index'),
            ]);
        }

        return to_route('admin.products.index')->with('status', 'Tạo sản phẩm thành công.');
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->persist($request, $product);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công.',
                'redirect' => route('admin.products.index'),
            ]);
        }

        return to_route('admin.products.index')->with('status', 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(Product $product)
    {
        $image = $product->image;
        app(ProductLifecycle::class)->delete($product);
        $this->removeOwnedImage($image);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công.',
                'redirect' => route('admin.products.index'),
            ]);
        }

        return to_route('admin.products.index')->with('status', 'Xóa sản phẩm thành công.');
    }

    public function stopSelling(Product $product)
    {
        return $this->setSelling($product, false);
    }

    public function resumeSelling(Product $product)
    {
        return $this->setSelling($product, true);
    }

    private function setSelling(Product $product, bool $selling)
    {
        $result = DB::connection('mongodb')->getDatabase()->products->updateOne(
            ['_id' => new ObjectId((string) $product->id)],
            ['$set' => ['is_selling' => $selling, 'updated_at' => new UTCDateTime], '$inc' => ['lifecycle_revision' => 1]],
        );
        abort_if($result->getMatchedCount() !== 1, 409, 'Sản phẩm đã thay đổi. Vui lòng tải lại trang.');

        $msg = ($selling ? 'Đã bán lại sản phẩm: ' : 'Đã ngừng bán sản phẩm: ').$product->name.'. Giá, tồn kho và ảnh được giữ nguyên.';
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect' => route('admin.products.index'),
            ]);
        }

        return to_route('admin.products.index')->with('status', $msg);
    }

    private function persist(ProductRequest $request, Product $product): void
    {
        $data = $request->safe()->except(['image', 'category_id']);
        $category = Category::findOrFail($request->validated('category_id'));
        if (! $category->code || ! $category->slug) {
            throw ValidationException::withMessages(['category_id' => 'Danh mục cần có code và slug theo schema MongoDB.']);
        }
        $data['category_id'] = (string) $category->getKey();
        $data['category'] = $category->only(['code', 'name', 'slug']);
        $data['brand'] = $data['brand'] ?? '';
        $data['description'] = $data['description'] ?? '';
        // Multipart browser forms send numeric inputs as strings; BSON validators require numbers.
        $data['price'] = (float) $data['price'];
        $data['stock'] = (int) $data['stock'];
        $oldImage = $product->image;
        $newImage = null;
        try {
            if ($request->hasFile('image')) {
                $newImage = Storage::disk('public')->putFile('products', $request->file('image'));
                if ($newImage === false) {
                    throw ValidationException::withMessages(['image' => 'Không thể lưu ảnh sản phẩm.']);
                }
                $data['image'] = $newImage;
            }
            if ($product->exists) {
                // Compare and write atomically, so another request's image cannot become orphaned.
                $data['updated_at'] = new UTCDateTime;
                $result = DB::connection('mongodb')->getDatabase()->products->updateOne(
                    $this->originalFilter($product), ['$set' => $data],
                );
                abort_if($result->getMatchedCount() !== 1, 409, 'Sản phẩm đã thay đổi. Vui lòng tải lại trang.');
            } else {
                $product->fill($data)->save();
            }
        } catch (Throwable $exception) {
            if ($newImage) {
                $this->removeOwnedImage($newImage);
            }
            throw $exception;
        }
        if ($newImage && $newImage !== $oldImage) {
            $this->removeOwnedImage($oldImage);
        }
    }

    private function originalFilter(Product $product): array
    {
        return [
            '_id' => new ObjectId((string) $product->getKey()),
            'image' => $product->getRawOriginal('image'),
            'updated_at' => $product->updated_at ? new UTCDateTime($product->updated_at) : null,
        ];
    }

    private function removeOwnedImage(?string $image): void
    {
        if (! $image || ! preg_match('~^products/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$~i', $image)) {
            return;
        }
        try {
            if (! Product::where('image', $image)->exists() && ! Storage::disk('public')->delete($image)) {
                logger()->warning('Không thể xóa ảnh sản phẩm không còn sử dụng.', ['image' => $image]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
