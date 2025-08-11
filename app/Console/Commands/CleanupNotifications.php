<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupNotifications extends Command
{
    protected $signature = 'notifications:cleanup';
    protected $description = 'Очистка старых уведомлений';

    public function handle()
    {
        $cutoffDate = Carbon::now()->subMonths(6);

        $deletedCount = Notification::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Удалено {$deletedCount} старых уведомлений (старше 6 месяцев)");
    }
}
