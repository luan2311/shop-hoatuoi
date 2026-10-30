<?php

namespace Tests\Unit;

use App\Support\Seeding\FlowerDataset;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FlowerDatasetTest extends TestCase
{
    public function test_source_dataset_has_real_expected_counts_and_consistent_embedded_categories(): void
    {
        $data = app(FlowerDataset::class)->load();
        $this->assertCount(5, $data['collections']['categories']);
        $this->assertCount(80, $data['collections']['products']);
        $this->assertCount(3, $data['collections']['posts']);
        $this->assertSame(hash('sha256', str_replace("\r\n", "\n", file_get_contents(database_path('mongodb_init.js')))), $data['source']['sha256_lf']);
        foreach ($data['collections']['products'] as $product) {
            $this->assertIsInt($product['price']);
            $this->assertGreaterThanOrEqual(0, $product['stock']);
            $this->assertTrue(FlowerDataset::validImage($product['image']));
        }
    }

    #[DataProvider('invalidDocuments')]
    public function test_invalid_dataset_is_rejected_before_an_import(string $collection, string $field, mixed $value): void
    {
        $dataset = app(FlowerDataset::class);
        $data = $dataset->load()['collections'];
        if ($value === '__remove__') {
            unset($data[$collection][0][$field]);
        } else {
            $data[$collection][0][$field] = $value;
        }
        $this->expectException(InvalidArgumentException::class);
        $dataset->validate($data);
    }

    public static function invalidDocuments(): array
    {
        return [
            'missing code' => ['products', 'code', '__remove__'],
            'missing name' => ['products', 'name', '__remove__'],
            'negative price' => ['products', 'price', -1],
            'fractional price' => ['products', 'price', 1.5],
            'formatted price' => ['products', 'price', '350.000 đ'],
            'too large price' => ['products', 'price', 1000000001],
            'negative stock' => ['products', 'stock', -1],
            'fractional stock' => ['products', 'stock', 1.5],
            'invalid status' => ['products', 'status', 'available'],
            'missing category' => ['products', 'category', '__remove__'],
            'unknown category' => ['products', 'category', ['code' => 'UNKNOWN']],
            'unsafe image' => ['products', 'image', '../private.jpg'],
            'external image' => ['products', 'image', 'https://example.test/image.jpg'],
            'invalid slug' => ['products', 'slug', 'Slug With Spaces'],
            'injected identifier' => ['products', '_id', '123'],
            'injected ownership' => ['products', 'sample_data', ['owner' => 'other']],
            'missing post content' => ['posts', 'content', '__remove__'],
            'invalid post date' => ['posts', 'published_at', ['$date' => '2024-02-31T00:00:00.000Z']],
            'category string' => ['products', 'category', 'LH001'],
            'oversized name' => ['products', 'name', str_repeat('Đ', 256)],
            'invalid utf8 name' => ['products', 'name', "\xff"],
            'control character name' => ['products', 'name', "Hoa\0hong"],
            'oversized description' => ['products', 'description', str_repeat('x', 100001)],
            'description wrong type' => ['products', 'description', ['html']],
            'brand wrong type' => ['products', 'brand', ['Blossom']],
            'oversized code' => ['products', 'code', str_repeat('A', 65)],
            'oversized slug' => ['products', 'slug', str_repeat('a', 256)],
            'windows reserved image' => ['products', 'image', 'CON.jpg'],
            'post date wrong type' => ['posts', 'published_at', '2024-09-24'],
        ];
    }

    public function test_duplicate_codes_and_slugs_are_rejected(): void
    {
        $dataset = app(FlowerDataset::class);
        foreach (['code', 'slug'] as $field) {
            $data = $dataset->load()['collections'];
            $data['products'][1][$field] = $data['products'][0][$field];
            try {
                $dataset->validate($data);
                $this->fail('Dataset trùng phải bị từ chối.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('trùng', $exception->getMessage());
            }
        }
    }

    public function test_manifest_reports_missing_assets_without_claiming_image_completion(): void
    {
        $manifest = json_decode(file_get_contents(base_path('docs/dung/product-images.json')), true, flags: JSON_THROW_ON_ERROR);
        $sourceAudit = json_decode(file_get_contents(base_path('docs/dung/source-audit.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(80, $manifest);
        $this->assertCount(74, array_unique(array_column($manifest, 'image')));
        $verifiedProductPhotos = 0;
        foreach ($manifest as $image) {
            $this->assertTrue($image['valid_filename']);
            $this->assertSame(is_file(base_path($image['asset_path'])), $image['exists']);
            $this->assertSame(is_file(base_path($image['storage_path'])), $image['runtime_exists']);
            if (! $image['exists']) {
                $this->assertSame('missing', $image['status']);

                continue;
            }
            if ($image['role'] === 'placeholder') {
                $this->assertSame('placeholder', $image['status']);
            }
            if ($image['role'] === 'illustration') {
                $this->assertSame('illustration', $image['status']);
            }
            if ($image['status'] === 'present') {
                $verifiedProductPhotos++;
                $this->assertSame('product', $image['role']);
                $this->assertContains($image['code'], $image['verified_product_codes']);
                $this->assertNotEmpty($image['author']);
                $this->assertContains($image['license'], ['CC0-1.0', 'CC-BY-4.0', 'CC-BY-SA-4.0', 'PROJECT-OWNED']);
                $this->assertStringStartsWith('https://', $image['source_url']);
            }
        }
        $uniquePhotos = [];
        foreach ($manifest as $image) {
            $uniquePhotos[$image['image']] = $image['status'];
        }
        $this->assertSame($sourceAudit['images']['present'], count(array_filter($uniquePhotos, fn (string $status): bool => $status === 'present')));
        $this->assertGreaterThanOrEqual($sourceAudit['images']['present'], $verifiedProductPhotos);
    }
}
