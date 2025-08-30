<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * ВСЕ ЗАДАЧИ ПЛАНИРОВЩИКА ПЕРЕНЕСЕНЫ В routes/console.php
     * для лучшей организации и оптимизации
     */
    protected function schedule(Schedule $schedule): void
    {
        // Все задачи планировщика находятся в routes/console.php
        // для централизованного управления и оптимизации
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
