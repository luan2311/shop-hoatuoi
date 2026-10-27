<?php

namespace App\Support\Seeding;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Database;
use Throwable;

final class SafeFlowerImporter
{
    public const OWNER = 'dung-flowers-v1';

    public function __construct(private FlowerDataset $dataset) {}

    public function run(string $confirmedDatabase, bool $apply = false, array $demoPasswords = []): array
    {
        // Từ chối trước cả thao tác ping/createCollection khi đích chưa được xác nhận.
        if (! app()->environment('local', 'testing') || config('database.default') !== 'mongodb') {
            throw new InvalidArgumentException('Chỉ cho phép APP_ENV=local/testing và DB_CONNECTION=mongodb.');
        }
        $configured = config('database.connections.mongodb.database');
        if ($confirmedDatabase === '' || $confirmedDatabase !== $configured
            || ! preg_match('/^shop_hoatuoi_(?:dung_dev(?:_[a-zA-Z0-9]+)*|test_[a-zA-Z0-9_]+)$/D', $confirmedDatabase)) {
            throw new InvalidArgumentException('Database phải khớp cấu hình và thuộc shop_hoatuoi_dung_dev hoặc shop_hoatuoi_test_*. Database dùng chung bị từ chối.');
        }
        $uri = config('database.connections.mongodb.dsn', '');
        if (! is_string($uri) || parse_url($uri, PHP_URL_SCHEME) !== 'mongodb'
            || ! in_array(parse_url($uri, PHP_URL_HOST), ['localhost', '127.0.0.1', '[::1]'], true)) {
            throw new InvalidArgumentException('Giai đoạn 1 chỉ nạp trên MongoDB loopback. Kết nối server từ xa cần nhóm thống nhất riêng.');
        }
        $data = $this->dataset->load();
        $collections = $data['collections'];
        if ($demoPasswords !== []) {
            foreach (['User', 'Admin'] as $role) {
                if (! is_string($demoPasswords[$role] ?? null) || strlen($demoPasswords[$role]) < 12) {
                    throw new InvalidArgumentException('Cần hai FLOWER_DEMO_*_PASSWORD riêng, mỗi giá trị ít nhất 12 ký tự.');
                }
            }
            $collections['users'] = array_map(fn ($role) => [
                'username' => 'dung_qa_'.strtolower($role),
                'name' => 'QA Dũng '.$role,
                'email' => strtolower($role).'.dung@example.test',
                'roles' => [$role], 'is_active' => true,
            ], ['User', 'Admin']);
        }
        $summary = ['status' => $apply ? 'PASS' : 'PREVIEW', 'database' => $confirmedDatabase,
            'mode' => $apply ? 'apply' : 'dry-run', 'source_sha256_lf' => $data['source']['sha256_lf'], 'collections' => []];
        foreach ($collections as $name => $documents) {
            $summary['collections'][$name] = ['inserted' => 0, 'skipped' => 0, 'updated' => 0, 'would_insert' => 0];
        }
        try {
            $db = DB::connection('mongodb')->getDatabase();
            if ($db->getDatabaseName() !== $confirmedDatabase) {
                throw new InvalidArgumentException('Database của driver không khớp đích đã xác nhận.');
            }
            $db->command(['ping' => 1])->toArray();
            // Kiểm tra toàn bộ xung đột định danh trước khi thêm bất kỳ dữ liệu nào.
            foreach ($collections as $name => $documents) {
                $collection = $db->selectCollection($name);
                foreach ($documents as $document) {
                    $key = $name === 'users' ? 'email' : 'code';
                    $alternates = $name === 'users' ? ['username'] : ['slug'];
                    $existing = $collection->findOne([$key => $document[$key]]);
                    foreach ($alternates as $alternate) {
                        $collision = $collection->findOne([$alternate => $document[$alternate]]);
                        if ($collision && (! $existing || (string) $collision['_id'] !== (string) $existing['_id'])) {
                            throw new InvalidArgumentException("$name: $alternate đã thuộc bản ghi khác; dừng để giữ dữ liệu hiện có.");
                        }
                    }
                    $idCollision = $collection->findOne(['_id' => $this->id($name, $document[$key])]);
                    if ($idCollision && (! $existing || (string) $idCollision['_id'] !== (string) $existing['_id'])) {
                        throw new InvalidArgumentException("$name: định danh mẫu đã được sử dụng; không ghi đè.");
                    }
                    $summary['collections'][$name][$existing ? 'skipped' : 'would_insert']++;
                }
            }
            // Danh mục nhúng mới dùng thông tin danh mục hiện có, không đổi danh mục của người khác.
            foreach ($collections['products'] as &$product) {
                $stored = $db->selectCollection('categories')->findOne(['code' => $product['category']['code']]);
                if ($stored) {
                    foreach (['code', 'name', 'slug'] as $field) {
                        if (! isset($stored[$field]) || ! is_string($stored[$field]) || $stored[$field] === '') {
                            throw new InvalidArgumentException('Danh mục hiện có không đủ code/name/slug; cần Lead xác nhận.');
                        }
                        $product['category'][$field] = $stored[$field];
                    }
                    $this->dataset->validateCategorySnapshot($product['category']);
                }
            }
            unset($product);
            if (! $apply) {
                return $summary;
            }
            $this->ensureIndexes($db, array_keys($collections));
            foreach ($collections as $name => $documents) {
                // Đếm lại từ kết quả upsert để xử lý cả trường hợp chạy đồng thời.
                $summary['collections'][$name]['skipped'] = 0;
                foreach ($documents as $document) {
                    $key = $name === 'users' ? 'email' : 'code';
                    $document = $this->dataset->runtimeDocument($document);
                    $document['_id'] = $this->id($name, $document[$key]);
                    $document['sample_data'] = ['owner' => self::OWNER, 'source_sha256_lf' => $data['source']['sha256_lf']];
                    $document['created_at'] = $document['updated_at'] = new UTCDateTime;
                    if ($name === 'users') {
                        $document['password'] = Hash::make($demoPasswords[$document['roles'][0]]);
                    }
                    // Không có $set, replace, delete hoặc reset stock; kể cả bản ghi mẫu cũ cũng được giữ nguyên.
                    $result = $db->selectCollection($name)->updateOne([$key => $document[$key]], ['$setOnInsert' => $document], ['upsert' => true]);
                    $summary['collections'][$name][$result->getUpsertedCount() ? 'inserted' : 'skipped']++;
                }
                $summary['collections'][$name]['would_insert'] = 0;
            }

            return $summary;
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        } catch (Throwable) {
            $summary['status'] = 'FAIL';
            throw new SampleImportException('Nạp dữ liệu không hoàn tất. Kiểm tra kết nối/index/validator; xem số bản ghi đã thêm trước khi chạy lại. Không có thao tác xóa hoặc cập nhật bản ghi cũ.', $summary);
        }
    }

    private function ensureIndexes(Database $db, array $names): void
    {
        foreach ($names as $name) {
            foreach ($name === 'users' ? ['email', 'username'] : ['code', 'slug'] as $field) {
                $db->selectCollection($name)->createIndex([$field => 1], ['unique' => true]);
            }
        }
        $hasText = false;
        foreach ($db->selectCollection('products')->listIndexes() as $index) {
            $hasText = $hasText || isset($index['weights']);
        }
        if (! $hasText) {
            $db->selectCollection('products')->createIndex(['name' => 'text', 'description' => 'text'], ['weights' => ['name' => 10, 'description' => 1], 'default_language' => 'none']);
        }
        $db->selectCollection('products')->createIndex(['category.code' => 1]);
        $db->selectCollection('products')->createIndex(['category.slug' => 1, 'price' => 1]);
        $db->selectCollection('posts')->createIndex(['published_at' => -1]);
    }

    private function id(string $collection, string $key): ObjectId
    {
        return new ObjectId(substr(hash('sha256', self::OWNER.':'.$collection.':'.$key), 0, 24));
    }
}
