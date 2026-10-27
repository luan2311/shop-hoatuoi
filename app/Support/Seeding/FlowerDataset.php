<?php

namespace App\Support\Seeding;

use DateTimeImmutable;
use InvalidArgumentException;
use MongoDB\BSON\UTCDateTime;

final class FlowerDataset
{
    public function load(): array
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/flowers.json')), true, flags: JSON_THROW_ON_ERROR);
        $source = str_replace("\r\n", "\n", file_get_contents(database_path('mongodb_init.js')));
        if (! hash_equals($data['source']['sha256_lf'], hash('sha256', $source))) {
            throw new InvalidArgumentException('Initializer đã thay đổi. Đối chiếu và trích xuất lại bộ dữ liệu trước khi nạp.');
        }
        $this->validate($data['collections']);

        return $data;
    }

    public function validate(array $collections): void
    {
        $names = array_keys($collections);
        sort($names);
        $this->require($names === ['categories', 'posts', 'products'], 'Phạm vi dataset chỉ gồm categories/products/posts.');
        $categories = [];
        foreach ($collections as $name => $documents) {
            $this->require(is_array($documents) && array_is_list($documents) && $documents !== [], "$name phải có danh sách dữ liệu.");
            $codes = $slugs = [];
            foreach ($documents as $document) {
                $this->require(is_array($document), "$name: document không hợp lệ.");
                $this->text($document['code'] ?? null, "$name.code", 64);
                $this->text($document['slug'] ?? null, "$name.slug", $name === 'categories' ? 120 : 255);
                $title = $name === 'posts' ? 'title' : 'name';
                $this->text($document[$title] ?? null, "$name.$title", $name === 'categories' ? 100 : 255);
                $code = $document['code'];
                $slug = $document['slug'];
                $this->require((bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/D', $code), "$name: mã không hợp lệ.");
                $this->require((bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug), "$name: slug không hợp lệ.");
                $this->require(! isset($codes[$code]) && ! isset($slugs[$slug]), "$name trùng code hoặc slug.");
                $this->require(! array_key_exists('_id', $document) && ! array_key_exists('sample_data', $document), "$name không nhận định danh runtime từ dataset.");
                $codes[$code] = $slugs[$slug] = true;
                if ($name === 'categories') {
                    $categories[$code] = $document;
                }
            }
        }
        foreach ($collections['products'] as $product) {
            $this->text($product['description'] ?? null, 'products.description', 100000, true);
            if (array_key_exists('brand', $product)) {
                $this->text($product['brand'], 'products.brand', 100);
            }
            $this->require(isset($product['price']) && is_int($product['price']) && $product['price'] >= 0 && $product['price'] <= 1000000000, 'Giá phải là số nguyên VND trong phạm vi bán hàng.');
            $this->require(isset($product['stock']) && is_int($product['stock']) && $product['stock'] >= 0, 'Tồn kho phải là số nguyên không âm.');
            $this->require(in_array($product['status'] ?? null, ['Còn hàng', 'Hết hàng'], true), 'Trạng thái sản phẩm không hợp lệ.');
            $this->require(self::validImage($product['image'] ?? null), 'Tên ảnh sản phẩm không hợp lệ.');
            $category = $product['category'] ?? [];
            $this->validateCategorySnapshot($category);
            $expected = $categories[$category['code'] ?? ''] ?? [];
            foreach (['code', 'name', 'slug'] as $key) {
                $this->require(isset($category[$key], $expected[$key]) && $category[$key] === $expected[$key], 'Danh mục nhúng không khớp categories.');
            }
            if (isset($product['original_price'])) {
                $this->require(is_int($product['original_price']) && $product['original_price'] >= 0, 'Giá gốc không hợp lệ.');
            }
        }
        foreach ($collections['posts'] as $post) {
            $this->text($post['content'] ?? null, 'posts.content', 200000, true);
            if (array_key_exists('summary', $post)) {
                $this->text($post['summary'], 'posts.summary', 5000, true);
            }
            $this->require(self::validImage($post['thumbnail'] ?? null), 'Tên ảnh bài viết không hợp lệ.');
            $this->require(is_array($post['published_at'] ?? null), 'Ngày đăng phải là document chứa $date.');
            $date = $post['published_at']['$date'] ?? '';
            $this->require(is_string($date) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/D', $date), 'Ngày đăng bài viết không hợp lệ.');
            $this->require((new DateTimeImmutable($date))->format('Y-m-d\TH:i:s.v\Z') === $date, 'Ngày đăng bài viết không tồn tại.');
        }
    }

    public static function validImage(mixed $image): bool
    {
        return is_string($image) && $image !== '' && strlen($image) <= 255
            && mb_check_encoding($image, 'UTF-8')
            && ! str_contains($image, '/') && ! str_contains($image, '\\')
            && ! str_contains($image, '..') && ! preg_match('/[\x00-\x1f<>:"|?*]/', $image)
            && ! preg_match('/^(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])\./i', $image)
            && (bool) preg_match('/\.(?:jpe?g|png|webp|gif|jfif)$/iD', $image);
    }

    public function runtimeDocument(array $document): array
    {
        if (isset($document['published_at']['$date'])) {
            $document['published_at'] = new UTCDateTime(new DateTimeImmutable($document['published_at']['$date']));
        }

        return $document;
    }

    public function validateCategorySnapshot(mixed $category): void
    {
        $this->require(is_array($category) && ! array_is_list($category), 'Danh mục phải là document code/name/slug.');
        foreach (['code' => 64, 'name' => 100, 'slug' => 120] as $field => $limit) {
            $this->text($category[$field] ?? null, 'category.'.$field, $limit);
        }
        $this->require((bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/D', $category['code']), 'Mã danh mục không hợp lệ.');
        $this->require((bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $category['slug']), 'Slug danh mục không hợp lệ.');
    }

    private function text(mixed $value, string $label, int $limit, bool $multiline = false): void
    {
        $this->require(is_string($value) && mb_check_encoding($value, 'UTF-8') && trim($value) !== '', "$label phải là chuỗi UTF-8 có nội dung.");
        $this->require(mb_strlen($value, 'UTF-8') <= $limit, "$label vượt quá $limit ký tự.");
        $controls = $multiline ? '/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/' : '/[\x00-\x1f\x7f]/';
        $this->require(! preg_match($controls, $value), "$label chứa ký tự điều khiển.");
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new InvalidArgumentException($message);
        }
    }
}
