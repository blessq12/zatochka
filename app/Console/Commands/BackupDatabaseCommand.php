<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--compress : Сжать бэкап в архив}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать резервную копию базы данных';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Начинаем создание резервной копии базы данных...');

        try {
            // Получаем конфигурацию БД
            $connection = config('database.default');
            $database = config("database.connections.{$connection}.database");
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");

            // Создаем имя файла
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$database}_{$timestamp}.sql";
            $backupPath = storage_path("app/backups/{$filename}");

            // Создаем директорию если не существует
            if (!file_exists(dirname($backupPath))) {
                mkdir(dirname($backupPath), 0755, true);
            }

            // Команда для mysqldump
            $command = "mysqldump --host={$host} --port={$port} --user={$username}";

            if ($password) {
                $command .= " --password={$password}";
            }

            $command .= " --single-transaction --routines --triggers {$database} > {$backupPath}";

            // Выполняем команду
            $this->info("📦 Выполняем mysqldump...");
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("Ошибка при создании бэкапа. Код возврата: {$returnCode}");
            }

            // Проверяем размер файла
            $fileSize = filesize($backupPath);
            if ($fileSize === 0) {
                throw new \Exception("Созданный файл бэкапа пуст");
            }

            $this->info("✅ Бэкап создан: {$filename} ({$this->formatBytes($fileSize)})");

            // Сжатие если требуется
            if ($this->option('compress')) {
                $this->info("🗜️ Сжимаем бэкап...");
                $this->compressBackup($backupPath);
            }

            // Очистка старых бэкапов
            $this->cleanOldBackups();

            // Логируем успешное создание
            Log::info('Database backup created successfully', [
                'filename' => $filename,
                'size' => $fileSize,
                'compressed' => $this->option('compress')
            ]);

            $this->info("🎉 Резервная копия успешно создана!");
        } catch (\Exception $e) {
            $this->error("❌ Ошибка при создании бэкапа: " . $e->getMessage());
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Сжать бэкап в архив
     */
    private function compressBackup(string $backupPath): void
    {
        $archivePath = $backupPath . '.gz';

        $command = "gzip -f {$backupPath}";
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка при сжатии бэкапа");
        }

        $compressedSize = filesize($archivePath);
        $this->info("✅ Бэкап сжат: " . basename($archivePath) . " ({$this->formatBytes($compressedSize)})");
    }

    /**
     * Очистить старые бэкапы (оставить только последние 7)
     */
    private function cleanOldBackups(): void
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/*.sql*');

        if (count($files) > 7) {
            // Сортируем по времени создания
            usort($files, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Удаляем старые файлы
            $filesToDelete = array_slice($files, 0, count($files) - 7);

            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->line("🗑️ Удален старый бэкап: " . basename($file));
            }

            $this->info("🧹 Очищено старых бэкапов: " . count($filesToDelete));
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
