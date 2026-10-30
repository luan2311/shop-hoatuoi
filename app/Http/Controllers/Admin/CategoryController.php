<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryBrowseRequest;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryUsage;
use App\Support\AdminPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\Regex;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CategoryBrowseRequest $request, CategoryUsage $service)
    {
        $perPage = AdminPagination::perPage($request);
        $usage = $service->snapshot();
        $query = Category::query();
        if (($search = trim($request->validated('search') ?? '')) !== '') {
            $literal = new Regex(preg_quote($search), 'i');
            $query->where(fn ($q) => $q->where('name', 'regex', $literal)->orWhere('code', 'regex', $literal)->orWhere('slug', 'regex', $literal));
        }
        if ($state = $request->validated('usage')) {
            $ids = array_keys(array_filter($usage['counts'], fn ($count) => $state === 'used' ? $count > 0 : $count === 0));
            $query->whereIn('_id', $ids);
        }
        $sort = $request->validated('sort') ?? 'newest';
        if (str_starts_with($sort, 'count-')) {
            $items = $query->get()->sort(function ($a, $b) use ($sort, $usage) {
                $result = $usage['counts'][(string) $a->id] <=> $usage['counts'][(string) $b->id];

                return ($sort === 'count-desc' ? -$result : $result) ?: strcmp((string) $a->id, (string) $b->id);
            })->values();
            $page = (int) ($request->validated('page') ?? 1);
            $categories = new LengthAwarePaginator($items->slice(($page - 1) * $perPage, $perPage)->values(), $items->count(), $perPage, $page, ['path' => $request->url()]);
        } else {
            [$column, $direction] = match ($sort) {
                'alpha-asc' => ['name', 'asc'], 'alpha-desc' => ['name', 'desc'], default => ['created_at', 'desc'],
            };
            $categories = $query->orderBy($column, $direction)->orderBy('_id')->paginate($perPage);
        }
        $categories->withQueryString();
        $total = count($usage['counts']);
        $used = count(array_filter($usage['counts'], fn ($count) => $count > 0));
        $stats = ['total' => $total, 'used' => $used, 'empty' => $total - $used];
        $deleteReasons = [];
        foreach ($categories as $category) {
            $deleteReasons[(string) $category->id] = $service->reason($category, $usage);
        }

        return view('admin.categories.index', compact('categories', 'usage', 'stats', 'deleteReasons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Thêm danh mục hoa thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Cập nhật danh mục hoa thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $service = app(CategoryUsage::class);
            if ($reason = $service->reason($category, $service->snapshot())) {
                throw ValidationException::withMessages(['category' => $reason]);
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['category' => 'Chưa xác minh được tham chiếu. Không thể xóa danh mục.']);
        }

        $category->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Xóa danh mục hoa thành công!', 'redirect' => route('admin.categories.index')]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Xóa danh mục hoa thành công!');
    }
}
