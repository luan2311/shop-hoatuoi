<?php

namespace Database\Seeders;

use App\Support\Seeding\SafeFlowerImporter;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class FlowerSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (! filter_var(env('FLOWER_SAMPLE_APPLY', false), FILTER_VALIDATE_BOOL)) {
            throw new InvalidArgumentException('Chưa bật FLOWER_SAMPLE_APPLY. Ưu tiên flowers:import-samples để xem trước.');
        }
        $passwords = filter_var(env('FLOWER_SAMPLE_DEMO_USERS', false), FILTER_VALIDATE_BOOL) ? [
            'User' => env('FLOWER_DEMO_USER_PASSWORD'), 'Admin' => env('FLOWER_DEMO_ADMIN_PASSWORD'),
        ] : [];
        $summary = app(SafeFlowerImporter::class)->run((string) env('FLOWER_SAMPLE_DATABASE', ''), true, $passwords);
        $this->command?->line(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
