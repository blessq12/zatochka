<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

// ========================================
// ОПТИМИЗИРОВАННЫЙ ПЛАНИРОВЩИК ЗАДАЧ
// ========================================

// ========================================
// БОНУСНАЯ СИСТЕМА
// ========================================

// Ежедневно в 08:00 - списание просроченных бонусов
Schedule::command('bonuses:expire')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        Log::info('Starting bonuses:expire task');
    })
    ->after(function () {
        Log::info('Completed bonuses:expire task');
    });

// Ежедневно в 09:00 - начисление бонусов за день рождения
Schedule::command('bonuses:birthday')
    ->dailyAt('09:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting bonuses:birthday task');
    })
    ->after(function () {
        \Log::info('Completed bonuses:birthday task');
    });

// Ежедневно в 10:00 - уведомления о скором истечении (за 7 дней)
Schedule::command('bonuses:notify-expiring --days=7')
    ->dailyAt('10:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting bonuses:notify-expiring task');
    })
    ->after(function () {
        \Log::info('Completed bonuses:notify-expiring task');
    });

// ========================================
// КЛИЕНТЫ И УВЕДОМЛЕНИЯ
// ========================================

// Ежедневно в 09:15 - поздравления с днем рождения (сдвинуто на 15 минут)
Schedule::command('clients:birthday-greetings')
    ->dailyAt('09:15')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting clients:birthday-greetings task');
    })
    ->after(function () {
        \Log::info('Completed clients:birthday-greetings task');
    });

// Еженедельно по понедельникам в 10:00 - напоминания о повторном визите
Schedule::command('clients:revisit-reminders')
    ->weekly()
    ->mondays()
    ->at('10:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting clients:revisit-reminders task');
    })
    ->after(function () {
        \Log::info('Completed clients:revisit-reminders task');
    });

// ========================================
// ЗАКАЗЫ И ОТЗЫВЫ
// ========================================

// Еженедельно по пятницам в 11:00 - запрос отзывов в 2ГИС
Schedule::command('orders:request-2gis-review')
    ->weekly()
    ->fridays()
    ->at('11:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting orders:request-2gis-review task');
    })
    ->after(function () {
        \Log::info('Completed orders:request-2gis-review task');
    });

// Ежедневно в 12:00 - получение отзывов из API сервисов
Schedule::command('reviews:fetch')
    ->dailyAt('12:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting reviews:fetch task');
    })
    ->after(function () {
        \Log::info('Completed reviews:fetch task');
    });

// ========================================
// СИСТЕМНЫЕ ЗАДАЧИ
// ========================================

// Ежемесячно в первое число в 02:00 - очистка старых уведомлений
Schedule::command('notifications:cleanup')
    ->monthly()
    ->at('02:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting notifications:cleanup task');
    })
    ->after(function () {
        \Log::info('Completed notifications:cleanup task');
    });

// Ежедневно в 03:00 - резервное копирование базы данных
Schedule::command('backup:run')
    ->dailyAt('03:00')
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting backup:run task');
    })
    ->after(function () {
        \Log::info('Completed backup:run task');
    });

// ========================================
// МОНИТОРИНГ ОЧЕРЕДЕЙ
// ========================================

// Каждые 10 минут - проверка и перезапуск очередей (оптимизировано)
Schedule::command('queue:check-n-restart')
    ->everyTenMinutes()
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting queue:check-n-restart task');
    })
    ->after(function () {
        \Log::info('Completed queue:check-n-restart task');
    });

// ========================================
// HEALTH CHECK
// ========================================

// Каждый час - проверка здоровья системы
Schedule::command('system:health-check')
    ->hourly()
    ->withoutOverlapping()

    ->onOneServer()
    ->runInBackground()
    ->before(function () {
        \Log::info('Starting system:health-check task');
    })
    ->after(function () {
        \Log::info('Completed system:health-check task');
    });
