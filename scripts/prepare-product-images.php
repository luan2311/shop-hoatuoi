<?php

use App\Support\Seeding\ProductImageLibrary;

require __DIR__.'/../vendor/autoload.php';
$root = dirname(__DIR__);
$options = getopt('', ['install-placeholders', 'install-assets', 'write-manifest', 'target:', 'input:', 'rights:', 'apply']);
try {
    if (! extension_loaded('gd')) {
        throw new RuntimeException('Cần PHP GD để kiểm tra và chuẩn hóa ảnh.');
    }
    $dataset = json_decode(file_get_contents($root.'/database/seeders/data/flowers.json'), true, flags: JSON_THROW_ON_ERROR)['collections'];
    $library = new ProductImageLibrary($root);
    $action = null;
    if (isset($options['install-placeholders'])) {
        $action = $library->installPlaceholders($dataset);
    }
    if (isset($options['install-assets'])) {
        $action = $library->installAssets($dataset);
    }
    if (isset($options['target'])) {
        $rights = json_decode(file_get_contents($options['rights'] ?? ''), true, flags: JSON_THROW_ON_ERROR);
        $action = $library->replace($dataset, $options['target'], $options['input'] ?? '', $rights, isset($options['apply']));
    }
    $audit = $library->audit($dataset);
    if (isset($options['write-manifest'])) {
        file_put_contents($root.'/docs/dung/image-audit.json', json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
    }
    echo json_encode(['status' => 'PASS', 'action' => $action, 'counts' => $audit['counts']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class === InvalidArgumentException::class ? $exception->getMessage().PHP_EOL : "Không thể xử lý ảnh. Kiểm tra file/metadata/quyền ghi; chưa xác nhận hoàn tất.\n");
    exit(1);
}
