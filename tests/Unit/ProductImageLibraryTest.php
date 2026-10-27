<?php

namespace Tests\Unit;

use App\Support\Seeding\ProductImageLibrary;
use InvalidArgumentException;
use Tests\TestCase;

class ProductImageLibraryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('BLOCKED: GD is required to verify image normalization.');
        }
        $this->root = base_path('.runtime/image-library-tests/'.bin2hex(random_bytes(8)));
        mkdir($this->root, 0777, true);
    }

    protected function tearDown(): void
    {
        if (isset($this->root) && is_dir($this->root)) {
            $this->removeTree($this->root);
        }
        parent::tearDown();
    }

    public function test_preview_apply_and_fresh_checkout_audit_keep_source_asset_rights_separate_from_runtime(): void
    {
        $library = new ProductImageLibrary($this->root);
        $dataset = $this->dataset(['P1']);
        $input = $this->makeJpeg('input.jpg', 240, 120);
        $relative = 'products/flower.jpg';

        $preview = $library->replace($dataset, $relative, $input, $this->rights(), false);
        $this->assertSame('PREVIEW', $preview['status']);
        $this->assertFileDoesNotExist($this->root.'/storage/app/public/'.$relative);
        $this->assertFileDoesNotExist($this->root.'/database/seeders/assets/'.$relative);

        $applied = $library->replace($dataset, $relative, $input, $this->rights(), true);
        $this->assertSame('PASS', $applied['status']);
        $this->assertSame(800, $applied['output']['width']);
        $this->assertSame(800, $applied['output']['height']);
        $this->assertLessThanOrEqual(350000, $applied['output']['bytes']);

        $runtime = $this->root.'/storage/app/public/'.$relative;
        unlink($runtime);
        $audit = $library->audit($dataset);
        $this->assertSame('illustration', $audit['files'][0]['role']);
        $this->assertSame('missing', $audit['files'][0]['runtime_state']);
        $this->assertSame(['illustration' => 1], $audit['counts']);

        $installed = $library->installAssets($dataset);
        $this->assertSame(1, $installed['installed']);
        $auditAfterInstall = $library->audit($dataset);
        $this->assertSame('illustration', $auditAfterInstall['files'][0]['role']);
        $this->assertSame('present', $auditAfterInstall['files'][0]['runtime_state']);
    }

    public function test_install_assets_replaces_only_the_registered_project_placeholder(): void
    {
        $library = new ProductImageLibrary($this->root);
        $dataset = $this->dataset(['P1']);
        $relative = 'products/flower.jpg';
        $library->installPlaceholders($dataset);
        $placeholder = file_get_contents($this->root.'/storage/app/public/'.$relative);
        $input = $this->makeJpeg('input.jpg', 80, 120);
        $library->replace($dataset, $relative, $input, $this->rights(), true);

        file_put_contents($this->root.'/storage/app/public/'.$relative, $placeholder);
        $result = $library->installAssets($dataset);

        $this->assertSame(1, $result['installed']);
        $assetHash = hash_file('sha256', $this->root.'/database/seeders/assets/'.$relative);
        $runtimeHash = hash_file('sha256', $this->root.'/storage/app/public/'.$relative);
        $this->assertSame($assetHash, $runtimeHash);
        $this->assertSame('present', $library->audit($dataset)['files'][0]['runtime_state']);
    }

    public function test_product_photos_must_be_verified_for_every_code_sharing_the_filename(): void
    {
        $library = new ProductImageLibrary($this->root);
        $dataset = $this->dataset(['P1', 'P2']);
        $input = $this->makeJpeg('input.jpg', 120, 80);
        $rights = $this->rights('product');
        $rights['verified_product_codes'] = ['P1'];

        $this->expectException(InvalidArgumentException::class);
        $library->replace($dataset, 'products/flower.jpg', $input, $rights, true);
    }

    public function test_unregistered_repository_asset_is_never_overwritten(): void
    {
        $library = new ProductImageLibrary($this->root);
        $dataset = $this->dataset(['P1']);
        $relative = 'products/flower.jpg';
        $input = $this->makeJpeg('input.jpg', 120, 80);
        $library->replace($dataset, $relative, $input, $this->rights(), true);
        $asset = $this->root.'/database/seeders/assets/'.$relative;
        $different = $this->makeJpeg('different.jpg', 90, 60);
        copy($different, $asset);
        $changedHash = hash_file('sha256', $asset);

        try {
            $library->replace($dataset, $relative, $input, $this->rights(), true);
            $this->fail('An unregistered repository asset must be protected.');
        } catch (InvalidArgumentException) {
            $this->assertSame($changedHash, hash_file('sha256', $asset));
        }
    }

    public function test_pexels_illustrations_keep_the_provider_license_and_install_on_a_fresh_checkout(): void
    {
        $library = new ProductImageLibrary($this->root);
        $rights = $this->rights();
        $rights['license'] = 'PEXELS-LICENSE';
        $rights['source_url'] = 'https://www.pexels.com/photo/flowers-in-basket-18476492/';
        $rights['license_url'] = 'https://www.pexels.com/license/';
        $dataset = $this->dataset(['P1']);
        $library->replace($dataset, 'products/flower.jpg', $this->makeJpeg('input.jpg', 120, 80), $rights, true);
        unlink($this->root.'/storage/app/public/products/flower.jpg');

        $this->assertSame(1, $library->installAssets($dataset)['installed']);
        $image = $library->audit($dataset)['files'][0];
        $this->assertSame('illustration', $image['role']);
        $this->assertSame('PEXELS-LICENSE', $image['provenance']['license']);
        $this->assertSame([], $image['provenance']['verified_product_codes']);
    }

    public function test_pexels_does_not_allow_product_verification_or_an_unrelated_source_or_license_url(): void
    {
        $library = new ProductImageLibrary($this->root);
        $input = $this->makeJpeg('input.jpg', 120, 80);
        foreach (['role' => 'product', 'source_url' => 'https://example.com/photo/flowers-123/', 'license_url' => 'https://example.com/license/'] as $field => $value) {
            $rights = array_replace($this->rights(), ['license' => 'PEXELS-LICENSE',
                'source_url' => 'https://www.pexels.com/photo/flowers-in-basket-18476492/',
                'license_url' => 'https://www.pexels.com/license/']);
            $rights[$field] = $value;
            try {
                $library->replace($this->dataset(['P1']), 'products/flower.jpg', $input, $rights);
                $this->fail('Invalid Pexels rights must be rejected.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('giấy phép', $exception->getMessage());
            }
        }
    }

    private function dataset(array $codes): array
    {
        return ['products' => array_map(fn (string $code) => ['code' => $code, 'image' => 'flower.jpg'], $codes), 'posts' => []];
    }

    private function rights(string $role = 'illustration'): array
    {
        return ['role' => $role, 'author' => 'QA Author', 'license' => 'CC-BY-4.0',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Flower.jpg',
            'license_url' => 'https://creativecommons.org/licenses/by/4.0/', 'attribution' => 'QA Author, CC BY 4.0.',
            'verified_product_codes' => []];
    }

    private function makeJpeg(string $filename, int $width, int $height): string
    {
        $path = $this->root.'/'.$filename;
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 90, 145, 110));
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $path;
    }

    private function removeTree(string $path): void
    {
        foreach (new \DirectoryIterator($path) as $entry) {
            if ($entry->isDot()) {
                continue;
            }
            $entryPath = $entry->getPathname();
            if ($entry->isDir() && ! $entry->isLink()) {
                $this->removeTree($entryPath);
            } else {
                unlink($entryPath);
            }
        }
        rmdir($path);
    }
}
