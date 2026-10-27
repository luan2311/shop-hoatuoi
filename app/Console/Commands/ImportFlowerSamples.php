<?php

namespace App\Console\Commands;

use App\Support\Seeding\SafeFlowerImporter;
use App\Support\Seeding\SampleImportException;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

class ImportFlowerSamples extends Command
{
    protected $signature = 'flowers:import-samples {--target= : Database đích đã xác nhận} {--apply : Thêm dữ liệu còn thiếu; mặc định chỉ xem trước} {--demo-users : Thêm hai tài khoản QA từ mật khẩu môi trường}';

    protected $description = 'Nạp dữ liệu hoa mẫu vào database riêng, giữ nguyên mọi bản ghi đã tồn tại';

    public function handle(SafeFlowerImporter $importer): int
    {
        try {
            $passwords = $this->option('demo-users') ? [
                'User' => env('FLOWER_DEMO_USER_PASSWORD'),
                'Admin' => env('FLOWER_DEMO_ADMIN_PASSWORD'),
            ] : [];
            $result = $importer->run((string) $this->option('target'), (bool) $this->option('apply'), $passwords);
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (SampleImportException $exception) {
            $this->line(json_encode($exception->summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->error($exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
        } catch (Throwable) {
            $this->error('Không thể chuẩn bị bộ dữ liệu. Kiểm tra source/dataset và dependency; không in chi tiết kết nối.');
        }

        return self::FAILURE;
    }
}
