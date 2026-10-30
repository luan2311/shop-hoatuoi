<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductBrowseRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Throwable;

class ProductController extends Controller
{
    public function index(ProductBrowseRequest $request)
    {
        $query = Product::query();
        if (($keyword = trim($request->validated('q') ?? '')) !== '') {
            $query->matchingKeyword($keyword);
        }

        return view('admin.products.index', ['products' => $query->orderBy('name')->orderBy('_id')->paginate(15)->withQueryString()]);
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
        $result = DB::connection('mongodb')->getDatabase()->products->deleteOne($this->originalFilter($product));
        abort_if($result->getDeletedCount() !== 1, 409, 'Sản phẩm đã thay đổi. Vui lòng tải lại trang.');
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
        $product->stock = 0;
        $product->status = 'Hết hàng';
        $product->save();

        $msg = 'Đã ngưng bán sản phẩm: '.$product->name.'. Tình trạng đã được cập nhật thành "Hết hàng".';
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
