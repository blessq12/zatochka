<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;
use App\Models\Order;
use App\Models\Client;
use App\Models\Review;

class HealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:check {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check system health and collect statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting system health check...');

        $checks = [
            'Database Connection' => $this->checkDatabase(),
            'Cache System' => $this->checkCache(),
            'Telegram Bot' => $this->checkTelegramBot(),
            'File Permissions' => $this->checkFilePermissions(),
            'System Statistics' => $this->collectStatistics(),
        ];

        $allPassed = true;
        $results = [];

        foreach ($checks as $checkName => $result) {
            $status = $result['status'] ? '✅' : '❌';
            $this->line("{$status} {$checkName}: {$result['message']}");

            if (!$result['status']) {
                $allPassed = false;
            }

            $results[$checkName] = $result;
        }

        // Логируем результаты
        Log::info('Health check completed', [
            'all_passed' => $allPassed,
            'results' => $results
        ]);

        if ($allPassed) {
            $this->info('🎉 All health checks passed!');
            return 0;
        } else {
            $this->error('⚠️ Some health checks failed!');
            return 1;
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $tables = DB::select('SHOW TABLES');

            return [
                'status' => true,
                'message' => 'Connected successfully (' . count($tables) . ' tables)',
                'data' => ['tables_count' => count($tables)]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => ['error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                return [
                    'status' => true,
                    'message' => 'Working correctly',
                    'data' => ['driver' => config('cache.default')]
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Cache read/write failed',
                    'data' => ['driver' => config('cache.default')]
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Cache error: ' . $e->getMessage(),
                'data' => ['error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Check Telegram bot
     */
    private function checkTelegramBot(): array
    {
        try {
            $telegramService = app(TelegramService::class);
            $isHealthy = $telegramService->checkBotHealth();

            if ($isHealthy) {
                $botInfo = $telegramService->getBotInfo();
                return [
                    'status' => true,
                    'message' => 'Bot is online',
                    'data' => $botInfo
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Bot is offline or unreachable',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Telegram check failed: ' . $e->getMessage(),
                'data' => ['error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Check file permissions
     */
    private function checkFilePermissions(): array
    {
        $paths = [
            storage_path('logs') => '0755',
            storage_path('app') => '0755',
            storage_path('framework/cache') => '0755',
            storage_path('framework/sessions') => '0755',
            storage_path('framework/views') => '0755',
        ];

        $failed = [];

        foreach ($paths as $path => $expectedPerms) {
            if (!is_dir($path)) {
                $failed[] = "Directory not found: {$path}";
                continue;
            }

            $perms = substr(sprintf('%o', fileperms($path)), -4);
            if ($perms !== $expectedPerms) {
                $failed[] = "Wrong permissions on {$path}: {$perms} (expected {$expectedPerms})";
            }
        }

        if (empty($failed)) {
            return [
                'status' => true,
                'message' => 'All permissions are correct',
                'data' => ['checked_paths' => count($paths)]
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Permission issues found',
                'data' => ['issues' => $failed]
            ];
        }
    }

    /**
     * Collect system statistics
     */
    private function collectStatistics(): array
    {
        try {
            $stats = [
                'total_orders' => Order::count(),
                'total_clients' => Client::count(),
                'total_reviews' => Review::count(),
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'orders_this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'orders_this_month' => Order::whereMonth('created_at', now()->month)->count(),
            ];

            return [
                'status' => true,
                'message' => 'Statistics collected successfully',
                'data' => $stats
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Failed to collect statistics: ' . $e->getMessage(),
                'data' => ['error' => $e->getMessage()]
            ];
        }
    }
}
