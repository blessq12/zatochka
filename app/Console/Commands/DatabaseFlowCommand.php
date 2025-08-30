<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:flow
                            {action : Действие (fresh|seed|demo|clean-demo|full-reset)}
                            {--force : Принудительное выполнение без подтверждения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Полный флоу работы с базой данных: очистка, сидеры, демо данные';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $force = $this->option('force');

        switch ($action) {
            case 'fresh':
                $this->freshDatabase($force);
                break;
            case 'seed':
                $this->runSeeders($force);
                break;
            case 'demo':
                $this->fillDemoData($force);
                break;
            case 'clean-demo':
                $this->cleanDemoData($force);
                break;
            case 'full-reset':
                $this->fullReset($force);
                break;
            default:
                $this->error('Неизвестное действие. Доступные: fresh, seed, demo, clean-demo, full-reset');
                return 1;
        }

        return 0;
    }

    /**
     * Полная очистка и миграция базы данных
     */
    private function freshDatabase(bool $force = false): void
    {
        if (!$force && !$this->confirm('⚠️  Это удалит ВСЕ данные из базы! Продолжить?')) {
            $this->info('❌ Операция отменена');
            return;
        }

        $this->info('🗑️  Очищаем базу данных...');
        $this->call('migrate:fresh');
        $this->info('✅ База данных очищена и мигрирована');
    }

    /**
     * Запуск сидеров
     */
    private function runSeeders(bool $force = false): void
    {
        if (!$force && !$this->confirm('Запустить сидеры для заполнения базовых данных?')) {
            $this->info('❌ Операция отменена');
            return;
        }

        $this->info('🌱 Запускаем сидеры...');
        $this->call('db:seed');
        $this->info('✅ Сидеры выполнены');
    }

    /**
     * Заполнение демо данными
     */
    private function fillDemoData(bool $force = false): void
    {
        if (!$force && !$this->confirm('Добавить демо данные (клиенты, заказы, отзывы)?')) {
            $this->info('❌ Операция отменена');
            return;
        }

        $this->info('🎭 Заполняем демо данными...');
        $this->call('app:fill-demo-data', ['--force' => true]);
        $this->info('✅ Демо данные добавлены');
    }

    /**
     * Очистка демо данных
     */
    private function cleanDemoData(bool $force = false): void
    {
        if (!$force && !$this->confirm('Удалить демо данные (клиенты, заказы, отзывы)?')) {
            $this->info('❌ Операция отменена');
            return;
        }

        $this->info('🧹 Удаляем демо данные...');

        try {
            // Отключаем проверку внешних ключей
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Удаляем демо пользователей (кроме админа)
            \App\Models\User::where('email', '!=', 'admin@zatochka.org')->delete();

            // Удаляем все демо данные
            \App\Models\ClientBonus::truncate();
            \App\Models\Review::truncate();
            \App\Models\Repair::truncate();
            \App\Models\OrderTool::truncate();
            \App\Models\Order::truncate();
            \App\Models\Client::truncate();

            // Включаем проверку внешних ключей
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Демо данные удалены');
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при удалении демо данных: ' . $e->getMessage());
        }
    }

    /**
     * Полный сброс: очистка + сидеры + демо
     */
    private function fullReset(bool $force = false): void
    {
        if (!$force && !$this->confirm('⚠️  Полный сброс базы данных? Это удалит ВСЕ данные и создаст новые!')) {
            $this->info('❌ Операция отменена');
            return;
        }

        $this->info('🔄 Начинаем полный сброс базы данных...');

        // 1. Очистка
        $this->info('1️⃣ Очищаем базу...');
        $this->call('migrate:fresh');

        // 2. Сидеры
        $this->info('2️⃣ Запускаем сидеры...');
        $this->call('db:seed');

        // 3. Демо данные
        $this->info('3️⃣ Добавляем демо данные...');
        $this->call('app:fill-demo-data', ['--force' => true]);

        $this->info('✅ Полный сброс завершен!');
        $this->displaySummary();
    }

    /**
     * Отображает сводку
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Доступные команды:');
        $this->table(
            ['Команда', 'Описание'],
            [
                ['php artisan db:flow fresh', 'Очистка и миграция БД'],
                ['php artisan db:flow seed', 'Запуск сидеров'],
                ['php artisan db:flow demo', 'Добавление демо данных'],
                ['php artisan db:flow clean-demo', 'Удаление демо данных'],
                ['php artisan db:flow full-reset', 'Полный сброс (все выше)'],
            ]
        );

        $this->newLine();
        $this->info('🔑 Данные для входа:');
        $this->info('Админ: admin@zatochka.org / password');
        $this->info('Демо: demo@zatochka.org / demo123');
    }
}
