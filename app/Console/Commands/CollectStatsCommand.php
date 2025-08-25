<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Client;
use App\Models\Review;
use App\Models\Notification;
use Carbon\Carbon;

class CollectStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:collect {--period=day : Period for statistics (day, week, month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect system statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $this->info("📊 Collecting statistics for period: {$period}");

        $stats = [
            'period' => $period,
            'collected_at' => now()->toISOString(),
            'orders' => $this->getOrderStats($period),
            'clients' => $this->getClientStats($period),
            'reviews' => $this->getReviewStats($period),
            'notifications' => $this->getNotificationStats($period),
            'system' => $this->getSystemStats(),
        ];

        // Выводим статистику
        $this->displayStats($stats);

        // Логируем статистику
        Log::info('Statistics collected', $stats);

        $this->info('✅ Statistics collected successfully!');
        return 0;
    }

    /**
     * Get order statistics
     */
    private function getOrderStats(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        $query = Order::whereBetween('created_at', $dateRange);

        return [
            'total' => $query->count(),
            'by_status' => $query->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_service_type' => $query->selectRaw('service_type, COUNT(*) as count')
                ->groupBy('service_type')
                ->pluck('count', 'service_type')
                ->toArray(),
            'total_amount' => $query->sum('total_amount'),
            'average_amount' => $query->avg('total_amount'),
            'with_delivery' => $query->where('needs_delivery', true)->count(),
        ];
    }

    /**
     * Get client statistics
     */
    private function getClientStats(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        $query = Client::whereBetween('created_at', $dateRange);

        return [
            'new_clients' => $query->count(),
            'with_telegram' => $query->whereNotNull('telegram')->count(),
            'telegram_verified' => $query->whereNotNull('telegram_verified_at')->count(),
            'active_clients' => $query->whereHas('orders')->count(),
        ];
    }

    /**
     * Get review statistics
     */
    private function getReviewStats(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        $query = Review::whereBetween('created_at', $dateRange);

        return [
            'total' => $query->count(),
            'approved' => $query->where('is_approved', true)->count(),
            'pending' => $query->where('is_approved', false)->count(),
            'average_rating' => $query->where('is_approved', true)->avg('rating'),
            'rating_distribution' => $query->where('is_approved', true)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
        ];
    }

    /**
     * Get notification statistics
     */
    private function getNotificationStats(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        $query = Notification::whereBetween('created_at', $dateRange);

        return [
            'total' => $query->count(),
            'read' => $query->where('is_read', true)->count(),
            'unread' => $query->where('is_read', false)->count(),
            'by_type' => $query->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        return [
            'database_size' => $this->getDatabaseSize(),
            'cache_hits' => 0, // Можно добавить если настроить кеш статистики
            'disk_usage' => $this->getDiskUsage(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    /**
     * Get date range for period
     */
    private function getDateRange(string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'week':
                return [$now->startOfWeek(), $now->endOfWeek()];
            case 'month':
                return [$now->startOfMonth(), $now->endOfMonth()];
            default:
                return [$now->startOfDay(), $now->endOfDay()];
        }
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $result = DB::select("
                SELECT
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ");

            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $storagePath = storage_path();
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'usage_percent' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Display statistics
     */
    private function displayStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Period', $stats['period']],
                ['Orders Total', $stats['orders']['total']],
                ['Orders Amount', number_format($stats['orders']['total_amount'], 2) . ' ₽'],
                ['New Clients', $stats['clients']['new_clients']],
                ['Reviews Total', $stats['reviews']['total']],
                ['Reviews Approved', $stats['reviews']['approved']],
                ['Notifications', $stats['notifications']['total']],
                ['Database Size', $stats['system']['database_size']],
                ['Disk Usage', $stats['system']['disk_usage']['usage_percent'] . '%'],
            ]
        );
    }
}
