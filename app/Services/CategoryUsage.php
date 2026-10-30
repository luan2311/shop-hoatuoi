<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class CategoryUsage
{
    /** Read-only batch audit: no backfill, saved counters, or per-category product queries. */
    public function snapshot(): array
    {
        $categories = Category::all();
        $ids = $codes = $slugs = [];
        $counts = $dependencies = [];
        foreach ($categories as $category) {
            $id = (string) $category->id;
            $ids[$id][] = $id;
            $codes[(string) $category->code][] = $id;
            $slugs[(string) $category->slug][] = $id;
            $counts[$id] = 0;
            $dependencies[$id] = false;
        }
        $uncertain = false;
        $conflicts = 0;
        $resolve = function ($document) use ($ids, $codes, $slugs, &$uncertain): array {
            $matches = [];
            $lookup = function ($value, $map) use (&$matches, &$uncertain) {
                if ((! is_string($value) && ! $value instanceof ObjectId) || (string) $value === '' || ! isset($map[(string) $value])) {
                    $uncertain = true;

                    return;
                }
                $matches = array_merge($matches, $map[(string) $value]);
            };
            if (isset($document['category_id'])) {
                // Also recognize legacy code-valued category_id, without rewriting it.
                $lookup($document['category_id'], array_merge_recursive($ids, $codes));
            }
            $embedded = $document['category'] ?? null;
            if (is_array($embedded) || $embedded instanceof \Traversable) {
                $embedded = is_array($embedded) ? $embedded : iterator_to_array($embedded);
                if ($embedded !== [] && ! array_intersect(['code', 'slug', '_id', 'id'], array_keys($embedded))) {
                    $uncertain = true;
                }
                if (isset($embedded['code'])) {
                    $lookup($embedded['code'], $codes);
                } elseif (isset($embedded['slug'])) {
                    $lookup($embedded['slug'], $slugs);
                }
                foreach (['_id', 'id'] as $key) {
                    if (isset($embedded[$key])) {
                        $lookup($embedded[$key], $ids);
                    }
                }
            } elseif ($embedded !== null) {
                $lookup($embedded, array_merge_recursive($ids, $codes, $slugs));
            }
            $matches = array_values(array_unique($matches));
            if ($matches === []) {
                $uncertain = true;
            }

            return $matches;
        };
        $db = DB::connection('mongodb')->getDatabase();
        foreach ($db->products->find([], ['projection' => ['category' => 1, 'category_id' => 1]]) as $product) {
            $matches = $resolve($product);
            if (count($matches) > 1) {
                $conflicts++;
            }
            foreach ($matches as $id) {
                $counts[$id]++;
            }
        }
        // Current models use Product as the category owner. Keep explicit legacy
        // category references in other collections protected if they are present.
        $hasCategory = ['$or' => [['category' => ['$exists' => true]], ['category_id' => ['$exists' => true]], ['items.category' => ['$exists' => true]], ['items.category_id' => ['$exists' => true]]]];
        foreach (['orders', 'carts', 'wishlists', 'posts', 'contacts'] as $name) {
            foreach ($db->$name->find($hasCategory, ['projection' => ['category' => 1, 'category_id' => 1, 'items.category' => 1, 'items.category_id' => 1]]) as $document) {
                $rows = [$document];
                foreach (($document['items'] ?? []) as $item) {
                    $rows[] = $item;
                }
                foreach ($rows as $row) {
                    if (! isset($row['category']) && ! isset($row['category_id'])) {
                        continue;
                    }
                    foreach ($resolve($row) as $id) {
                        $dependencies[$id] = true;
                    }
                }
            }
        }

        return compact('categories', 'counts', 'dependencies', 'uncertain', 'conflicts');
    }

    public function reason(Category $category, array $usage): ?string
    {
        $id = (string) $category->id;
        if (($count = $usage['counts'][$id] ?? 0) > 0) {
            return "Không thể xóa vì danh mục đang có {$count} sản phẩm.";
        }
        if ($usage['dependencies'][$id] ?? false) {
            return 'Không thể xóa vì danh mục còn tham chiếu phụ thuộc khác.';
        }
        if ($usage['uncertain'] || ! array_key_exists($id, $usage['counts'])) {
            return 'Không thể xóa vì chưa xác minh được tham chiếu danh mục legacy.';
        }

        return null;
    }
}
