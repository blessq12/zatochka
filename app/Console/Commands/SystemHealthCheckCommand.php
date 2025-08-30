<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check {--detailed : Подробный вывод}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка здоровья системы и мониторинг фоновых процессов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Проверка здоровья системы...');

        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'memory' => $this->checkMemory(),
        ];

        $failedChecks = array_filter($checks, fn($check) => !$check['status']);

        if (empty($failedChecks)) {
            $this->info('✅ Все системы работают нормально');
            Log::info('System health check passed');
            return 0;
        }

        $this->error('❌ Обнаружены проблемы:');
        foreach ($failedChecks as $check => $data) {
            $this->error("  - {$data['message']}");
        }

        Log::warning('System health check failed', ['failed_checks' => $failedChecks]);
        return 1;
    }

    /**
     * Проверка базы данных
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $queryTime = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $queryTime) * 1000;

            if ($queryTime > 1000) { // больше 1 секунды
                return [
                    'status' => false,
                    'message' => "База данных медленно отвечает ({$queryTime}ms)"
                ];
            }

            return ['status' => true, 'message' => 'База данных работает нормально'];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Проверка кэша
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value !== 'test') {
                return [
                    'status' => false,
                    'message' => 'Кэш не работает корректно'
                ];
            }

            return ['status' => true, 'message' => 'Кэш работает нормально'];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Ошибка кэша: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Проверка очередей
     */
    private function checkQueue(): array
    {
        try {
            // Проверяем количество задач в очереди
            $failedJobs = DB::table('failed_jobs')->count();

            if ($failedJobs > 10) {
                return [
                    'status' => false,
                    'message' => "Слишком много неудачных задач в очереди: {$failedJobs}"
                ];
            }

            return ['status' => true, 'message' => 'Очереди работают нормально'];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Ошибка проверки очередей: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Проверка хранилища
     */
    private function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

            if ($usedPercent > 90) {
                return [
                    'status' => false,
                    'message' => "Мало места на диске: {$usedPercent}% использовано"
                ];
            }

            return ['status' => true, 'message' => 'Место на диске в норме'];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Ошибка проверки хранилища: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Проверка памяти
     */
    private function checkMemory(): array
    {
        try {
            $memoryLimit = ini_get('memory_limit');
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);

            // Конвертируем memory_limit в байты
            $limitBytes = $this->convertToBytes($memoryLimit);
            $usagePercent = ($memoryPeak / $limitBytes) * 100;

            if ($usagePercent > 80) {
                return [
                    'status' => false,
                    'message' => "Высокое потребление памяти: {$usagePercent}%"
                ];
            }

            return ['status' => true, 'message' => 'Память в норме'];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Ошибка проверки памяти: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Конвертация строки памяти в байты
     */
    private function convertToBytes(string $memoryLimit): int
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        return match ($unit) {
            'k' => $value * 1024,
            'm' => $value * 1024 * 1024,
            'g' => $value * 1024 * 1024 * 1024,
            default => $value,
        };
    }
}
