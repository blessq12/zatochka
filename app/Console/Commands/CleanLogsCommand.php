<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean {--days=30 : Количество дней для хранения логов} {--force : Принудительная очистка}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистить старые логи';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $force = $this->option('force');

        $this->info("🧹 Начинаем очистку логов старше {$days} дней...");

        if (!$force) {
            if (!$this->confirm('Вы уверены, что хотите удалить старые логи?')) {
                $this->info('❌ Операция отменена');
                return 0;
            }
        }

        try {
            $cutoffDate = Carbon::now()->subDays($days);
            $deletedFiles = 0;
            $deletedSize = 0;

            // Очищаем Laravel логи
            $logPath = storage_path('logs');
            if (File::exists($logPath)) {
                $files = File::files($logPath);

                foreach ($files as $file) {
                    if ($file->getMTime() < $cutoffDate->timestamp) {
                        $size = $file->getSize();
                        File::delete($file->getPathname());
                        $deletedFiles++;
                        $deletedSize += $size;

                        $this->line("🗑️ Удален: " . $file->getFilename());
                    }
                }
            }

            // Очищаем кеш
            $this->cleanCache();

            // Очищаем временные файлы
            $this->cleanTempFiles($cutoffDate);

            $this->info("✅ Очистка завершена!");
            $this->info("📊 Удалено файлов: {$deletedFiles}");
            $this->info("📊 Освобождено места: {$this->formatBytes($deletedSize)}");

            // Логируем очистку
            Log::info('Logs cleaned successfully', [
                'deleted_files' => $deletedFiles,
                'deleted_size' => $deletedSize,
                'cutoff_days' => $days
            ]);
        } catch (\Exception $e) {
            $this->error("❌ Ошибка при очистке логов: " . $e->getMessage());
            Log::error('Logs cleaning failed', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Очистить кеш
     */
    private function cleanCache(): void
    {
        $this->info("🧹 Очищаем кеш...");

        $cachePath = storage_path('framework/cache');
        if (File::exists($cachePath)) {
            $files = File::files($cachePath);
            $deleted = 0;

            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    File::delete($file->getPathname());
                    $deleted++;
                }
            }

            $this->line("🗑️ Удалено кеш файлов: {$deleted}");
        }
    }

    /**
     * Очистить временные файлы
     */
    private function cleanTempFiles(Carbon $cutoffDate): void
    {
        $this->info("🧹 Очищаем временные файлы...");

        $tempPaths = [
            storage_path('app/temp'),
            storage_path('app/uploads/temp'),
        ];

        $deleted = 0;

        foreach ($tempPaths as $tempPath) {
            if (File::exists($tempPath)) {
                $files = File::files($tempPath);

                foreach ($files as $file) {
                    if ($file->getMTime() < $cutoffDate->timestamp) {
                        File::delete($file->getPathname());
                        $deleted++;
                    }
                }
            }
        }

        if ($deleted > 0) {
            $this->line("🗑️ Удалено временных файлов: {$deleted}");
        }
    }

    /**
     * Форматировать размер файла
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
