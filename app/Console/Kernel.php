<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ежедневно в 9:00 - поздравления с днем рождения
        $schedule->command('clients:birthday-greetings')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Еженедельно по понедельникам в 10:00 - напоминания о повторном визите
        $schedule->command('clients:revisit-reminders')
            ->weekly()
            ->mondays()
            ->at('10:00')
            ->withoutOverlapping()
            ->runInBackground();



        // Еженедельно по пятницам в 11:00 - запрос отзывов в 2ГИС (через 7 дней после доставки)
        $schedule->command('orders:request-2gis-review')
            ->weekly()
            ->fridays()
            ->at('11:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Очистка старых уведомлений (старше 6 месяцев)
        $schedule->command('notifications:cleanup')
            ->monthly()
            ->firstOfMonth()
            ->at('02:00')
            ->runInBackground();

        // Резервное копирование базы данных
        $schedule->command('backup:run')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground();
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
