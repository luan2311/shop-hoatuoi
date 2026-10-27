<?php

namespace App\Support\Seeding;

use InvalidArgumentException;
use RuntimeException;

final class ProductImageLibrary
{
    public function __construct(private string $root) {}

    public function inspect(string $path): array
    {
        if (! is_file($path)) {
            return ['exists' => false, 'valid' => false];
        }
        $bytes = filesize($path);
        if ($bytes === false || $bytes > 8 * 1024 * 1024) {
            return ['exists' => true, 'valid' => false, 'error' => 'file_too_large'];
        }
        $size = @getimagesize($path);
        if (! $size || ! in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)
            || $size[0] > 8000 || $size[1] > 8000 || $size[0] * $size[1] > 25000000) {
            return ['exists' => true, 'valid' => false, 'error' => 'invalid_or_oversized_image'];
        }
        $image = @imagecreatefromstring(file_get_contents($path));
        if (! $image) {
            return ['exists' => true, 'valid' => false, 'error' => 'decode_failed'];
        }
        imagedestroy($image);

        return ['exists' => true, 'valid' => true, 'width' => $size[0], 'height' => $size[1],
            'mime' => $size['mime'], 'bytes' => $bytes, 'sha256' => hash_file('sha256', $path)];
    }

    public function expected(array $collections): array
    {
        $result = [];
        foreach (['products' => 'image', 'posts' => 'thumbnail'] as $kind => $field) {
            foreach ($collections[$kind] as $document) {
                $filename = $document[$field];
                if (! FlowerDataset::validImage($filename)) {
                    throw new InvalidArgumentException('Tên ảnh không an toàn.');
                }
                $result[$kind.'/'.$filename][] = $document['code'];
            }
        }

        return $result;
    }

    public function installPlaceholders(array $collections): array
    {
        $template = $this->root.'/database/seeders/assets/placeholder.jpg';
        if (! is_file($template)) {
            $this->directory(dirname($template));
            $canvas = imagecreatetruecolor(800, 800);
            $background = imagecolorallocate($canvas, 243, 246, 244);
            $ink = imagecolorallocate($canvas, 61, 79, 67);
            $muted = imagecolorallocate($canvas, 178, 191, 182);
            imagefill($canvas, 0, 0, $background);
            imagesetthickness($canvas, 4);
            imagerectangle($canvas, 245, 170, 555, 430, $muted);
            imageellipse($canvas, 465, 240, 45, 45, $muted);
            imageline($canvas, 260, 410, 360, 295, $muted);
            imageline($canvas, 360, 295, 440, 375, $muted);
            imageline($canvas, 440, 375, 490, 325, $muted);
            imageline($canvas, 490, 325, 540, 410, $muted);
            foreach ([500 => 'PLACEHOLDER', 545 => 'PRODUCT PHOTO NOT PROVIDED', 590 => 'NOT AN ACTUAL PRODUCT IMAGE'] as $y => $line) {
                imagestring($canvas, 5, (int) ((800 - imagefontwidth(5) * strlen($line)) / 2), $y, $line, $ink);
            }
            imagejpeg($canvas, $template, 82);
            imagedestroy($canvas);
        }
        $ledger = $this->ledger();
        $created = $skipped = 0;
        foreach ($this->expected($collections) as $relative => $codes) {
            $path = $this->root.'/storage/app/public/'.$relative;
            if (file_exists($path) || is_link($path)) {
                $skipped++;

                continue;
            }
            $this->directory(dirname($path));
            // Tên nguồn hiện tại đều là JPEG. Không ghi JPEG giả dạng định dạng khác.
            if (! preg_match('/\.jpe?g$/iD', $relative)) {
                throw new InvalidArgumentException('Cần adapter ảnh cho định dạng khác JPEG.');
            }
            if (! copy($template, $path)) {
                throw new RuntimeException('Không ghi được ảnh placeholder.');
            }
            $ledger['files'][$relative] = ['role' => 'placeholder', 'sha256' => hash_file('sha256', $path),
                'author' => 'Nhóm dự án; mẫu trung tính do script tạo', 'license' => 'PROJECT-GENERATED',
                'source_url' => 'project://scripts/prepare-product-images.php', 'attribution' => '',
                'verified_product_codes' => [], 'expected_codes' => $codes];
            $created++;
        }
        $this->writeLedger($ledger);

        return ['created' => $created, 'skipped_existing' => $skipped, 'real_product_photos_created' => 0];
    }

    public function replace(array $collections, string $relative, string $incoming, array $rights, bool $apply = false): array
    {
        $expected = $this->expected($collections);
        if (! isset($expected[$relative])) {
            throw new InvalidArgumentException('Ảnh đích không thuộc dataset.');
        }
        $role = $rights['role'] ?? '';
        if (! in_array($role, ['illustration', 'product'], true)
            || ! in_array($rights['license'] ?? '', ['CC0-1.0', 'CC-BY-4.0', 'CC-BY-SA-4.0', 'PROJECT-OWNED'], true)
            || ! is_string($rights['author'] ?? null) || trim($rights['author']) === ''
            || ! is_string($rights['attribution'] ?? null)) {
            throw new InvalidArgumentException('Thiếu vai trò ảnh, tác giả, giấy phép hoặc ghi công.');
        }
        foreach (['source_url', 'license_url'] as $field) {
            $url = $rights[$field] ?? '';
            if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)
                || parse_url($url, PHP_URL_SCHEME) !== 'https' || parse_url($url, PHP_URL_USER)
                || parse_url($url, PHP_URL_PASS) || parse_url($url, PHP_URL_QUERY)) {
                throw new InvalidArgumentException('Cần URL nguồn/giấy phép công khai HTTPS, không credentials hoặc query riêng tư.');
            }
        }
        $verifiedCodes = $rights['verified_product_codes'] ?? [];
        if (! is_array($verifiedCodes) || array_filter($verifiedCodes, fn ($code) => ! is_string($code)) !== []) {
            throw new InvalidArgumentException('Danh sách mã sản phẩm đã xác nhận không hợp lệ.');
        }
        if ($role === 'product' && array_diff($expected[$relative], $verifiedCodes) !== []) {
            throw new InvalidArgumentException('Chưa xác nhận ảnh đúng cho mọi mã sản phẩm dùng chung tên file.');
        }
        $info = $this->inspect($incoming);
        if (! ($info['valid'] ?? false)) {
            throw new InvalidArgumentException('Ảnh đầu vào hỏng, quá lớn hoặc sai định dạng.');
        }
        if (! preg_match('/\.jpe?g$/iD', $relative)) {
            throw new InvalidArgumentException('Adapter hiện chuẩn hóa đầu ra JPEG.');
        }
        $ledger = $this->ledger();
        $target = $this->root.'/storage/app/public/'.$relative;
        $assetTarget = $this->root.'/database/seeders/assets/'.$relative;
        if (is_link($target) || (is_file($target) && ($ledger['files'][$relative]['sha256'] ?? '') !== hash_file('sha256', $target))) {
            throw new InvalidArgumentException('Không ghi đè file của người khác hoặc file đã thay đổi ngoài registry.');
        }
        $registeredAssetHash = $ledger['files'][$relative]['asset_sha256'] ?? '';
        if (is_link($assetTarget) || (is_file($assetTarget) && $registeredAssetHash !== hash_file('sha256', $assetTarget))) {
            throw new InvalidArgumentException('Không ghi đè asset trong repository nếu file chưa được registry xác nhận.');
        }
        if (! $apply) {
            return ['status' => 'PREVIEW', 'target' => $relative, 'role' => $role, 'input' => $info];
        }
        $this->directory(dirname($target));
        $this->directory(dirname($assetTarget));
        $image = imagecreatefromstring(file_get_contents($incoming));
        $canvas = imagecreatetruecolor(800, 800);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        $heightAvailable = $role === 'illustration' ? 740 : 800;
        $scale = min(800 / $info['width'], $heightAvailable / $info['height'], 1);
        $width = max(1, (int) round($info['width'] * $scale));
        $height = max(1, (int) round($info['height'] * $scale));
        imagecopyresampled($canvas, $image, (int) ((800 - $width) / 2), (int) (($heightAvailable - $height) / 2), 0, 0, $width, $height, $info['width'], $info['height']);
        if ($role === 'illustration') {
            imagefilledrectangle($canvas, 0, 740, 800, 800, imagecolorallocate($canvas, 239, 243, 240));
            imagestring($canvas, 5, 110, 760, 'ILLUSTRATION - PRODUCT MATCH NOT VERIFIED', imagecolorallocate($canvas, 50, 65, 55));
        }
        $temporary = tempnam(dirname($target), '.image-');
        try {
            if (! imagejpeg($canvas, $temporary, 82) || filesize($temporary) > 350000) {
                throw new RuntimeException('Không tạo được ảnh tối ưu trong giới hạn dung lượng.');
            }
            if (is_file($target) || is_file($assetTarget)) {
                $history = $this->root.'/.runtime/image-history';
                $this->directory($history);
                foreach ([$target, $assetTarget] as $previous) {
                    if (is_file($previous)) {
                        copy($previous, $history.'/'.hash_file('sha256', $previous).'.jpg');
                    }
                }
            }
            if (! copy($temporary, $assetTarget) || ! copy($temporary, $target)) {
                throw new RuntimeException('Không thể ghi asset và ảnh đích.');
            }
        } finally {
            imagedestroy($image);
            imagedestroy($canvas);
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
        $ledger['files'][$relative] = $rights;
        $ledger['files'][$relative]['expected_codes'] = $expected[$relative];
        $ledger['files'][$relative]['sha256'] = hash_file('sha256', $target);
        $ledger['files'][$relative]['asset_sha256'] = hash_file('sha256', $assetTarget);
        $this->writeLedger($ledger);

        return ['status' => 'PASS', 'target' => $relative, 'role' => $role, 'output' => $this->inspect($target)];
    }

    public function audit(array $collections): array
    {
        $ledger = $this->ledger();
        $files = [];
        foreach ($this->expected($collections) as $relative => $codes) {
            $runtime = $this->inspect($this->root.'/storage/app/public/'.$relative);
            $asset = $this->inspect($this->root.'/database/seeders/assets/'.$relative);
            $provenance = $ledger['files'][$relative] ?? [];
            $registeredRuntime = ($runtime['valid'] ?? false) && ($provenance['sha256'] ?? '') === ($runtime['sha256'] ?? null);
            $registeredAsset = ($asset['valid'] ?? false) && ($provenance['asset_sha256'] ?? '') === ($asset['sha256'] ?? null);
            $registered = $registeredAsset && ($provenance['expected_codes'] ?? []) === $codes;
            $runtimePlaceholder = $registeredRuntime && ($provenance['role'] ?? '') === 'placeholder';
            $role = $registered ? $provenance['role'] : ($runtimePlaceholder ? 'placeholder' : (($asset['exists'] ?? false) || ($runtime['exists'] ?? false) ? 'unverified' : 'missing'));
            $runtimeState = ! ($runtime['exists'] ?? false) ? 'missing' : (! ($runtime['valid'] ?? false) ? 'invalid'
                : ($registeredRuntime ? (($provenance['role'] ?? '') === 'placeholder' ? 'placeholder' : 'present') : 'unverified'));
            $files[] = ['path' => $relative, 'codes' => $codes, 'role' => $role, 'runtime_state' => $runtimeState,
                'provenance' => $registered || $runtimePlaceholder ? $provenance : null, 'asset' => $asset, 'image' => $runtime];
        }

        return ['files' => $files, 'counts' => array_count_values(array_column($files, 'role')),
            'runtime_counts' => array_count_values(array_column($files, 'runtime_state'))];
    }

    public function installAssets(array $collections): array
    {
        $ledger = $this->ledger();
        $result = ['installed' => 0, 'skipped' => 0, 'missing' => 0, 'rejected' => 0];
        $placeholder = $this->root.'/database/seeders/assets/placeholder.jpg';
        $placeholderHash = is_file($placeholder) ? hash_file('sha256', $placeholder) : null;
        foreach ($this->expected($collections) as $relative => $codes) {
            $asset = $this->root.'/database/seeders/assets/'.$relative;
            $target = $this->root.'/storage/app/public/'.$relative;
            $entry = $ledger['files'][$relative] ?? [];
            if (! is_file($asset)) {
                $result['missing']++;

                continue;
            }
            $info = $this->inspect($asset);
            $license = $entry['license'] ?? '';
            $role = $entry['role'] ?? '';
            $registered = ($info['valid'] ?? false)
                && ($entry['asset_sha256'] ?? '') === ($info['sha256'] ?? null)
                && ($entry['expected_codes'] ?? []) === $codes
                && in_array($role, ['product', 'illustration'], true)
                && in_array($license, ['CC0-1.0', 'CC-BY-4.0', 'CC-BY-SA-4.0', 'PROJECT-OWNED'], true)
                && is_string($entry['author'] ?? null) && trim($entry['author']) !== ''
                && is_string($entry['source_url'] ?? null) && filter_var($entry['source_url'], FILTER_VALIDATE_URL)
                && is_string($entry['license_url'] ?? null) && filter_var($entry['license_url'], FILTER_VALIDATE_URL);
            if ($role === 'product' && array_diff($codes, $entry['verified_product_codes'] ?? []) !== []) {
                $registered = false;
            }
            if (! $registered || is_link($target)) {
                $result['rejected']++;

                continue;
            }
            $assetHash = $info['sha256'];
            if (is_file($target)) {
                $runtimeHash = hash_file('sha256', $target);
                if ($runtimeHash === $assetHash) {
                    $ledger['files'][$relative]['sha256'] = $runtimeHash;
                    $result['skipped']++;
                } elseif ($placeholderHash !== null && $runtimeHash === $placeholderHash) {
                    if (copy($asset, $target)) {
                        $ledger['files'][$relative]['sha256'] = $assetHash;
                        $result['installed']++;
                    } else {
                        $result['rejected']++;
                    }
                } else {
                    $result['rejected']++;
                }

                continue;
            }
            $this->directory(dirname($target));
            if (! copy($asset, $target)) {
                $result['rejected']++;

                continue;
            }
            $ledger['files'][$relative]['sha256'] = $assetHash;
            $result['installed']++;
        }

        if ($result['installed'] > 0 || $result['skipped'] > 0) {
            $this->writeLedger($ledger);
        }

        return $result;
    }

    public function ledger(): array
    {
        $path = $this->root.'/database/seeders/data/image-provenance.json';

        return is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : ['version' => 1, 'files' => []];
    }

    private function writeLedger(array $ledger): void
    {
        $path = $this->root.'/database/seeders/data/image-provenance.json';
        $this->directory(dirname($path));
        file_put_contents($path, json_encode($ledger, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
    }

    private function directory(string $path): void
    {
        if (! is_dir($path) && ! mkdir($path, 0777, true) && ! is_dir($path)) {
            throw new RuntimeException('Không tạo được thư mục ảnh.');
        }
    }
}
