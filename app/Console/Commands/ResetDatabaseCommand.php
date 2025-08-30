<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ResetDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-database
                            {--demo-only : Clear only demo data, keep system data}
                            {--force : Force reset without confirmation}
                            {--seed : Run seeders after reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Полная очистка базы данных с возможностью выбора режима';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $demoOnly = $this->option('demo-only');
        $force = $this->option('force');
        $seed = $this->option('seed');

        if ($demoOnly) {
            $this->info('🧹 Очистка только демо-данных...');
            return $this->clearDemoData($force);
        }

        if (!$force) {
            $this->warn('⚠️  ВНИМАНИЕ! Эта команда полностью очистит базу данных!');

            if (!$this->confirm('🤯 Вы уверены, что хотите удалить ВСЕ данные из базы?')) {
                $this->info('❌ Операция отменена.');
                return 0;
            }

            if (!$this->confirm('💀 Это действие НЕОБРАТИМО! Продолжить?')) {
                $this->info('❌ Операция отменена.');
                return 0;
            }
        }

        $this->info('🔥 Начинаем полную очистку базы данных...');

        try {
            // Полная очистка БД
            $this->fullReset();

            if ($seed) {
                $this->info('🌱 Запускаем сидеры...');
                try {
                    Artisan::call('db:seed');
                    $this->info('✅ Сидеры выполнены!');
                } catch (\Exception $e) {
                    $this->warn('⚠️  Ошибка при запуске сидеров: ' . $e->getMessage());
                    $this->info('💡 Попробуйте запустить сидеры отдельно: php artisan db:seed');
                }
            }

            $this->info('✅ База данных полностью очищена!');
            $this->displayFinalSummary();
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при очистке базы данных: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Очищает только демо-данные
     */
    private function clearDemoData(bool $force): int
    {
        $command = 'app:clear-demo-data';
        if ($force) {
            $command .= ' --force';
        }

        return Artisan::call($command);
    }

    /**
     * Полная очистка базы данных
     */
    private function fullReset(): void
    {
        $this->info('🗑️  Удаляем все таблицы...');

        // Отключаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Получаем список всех таблиц
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            if ($tableName !== 'migrations') {
                $tableNames[] = $tableName;
            }
        }

        // Удаляем все таблицы
        foreach ($tableNames as $tableName) {
            $this->info("   Удаляем таблицу: {$tableName}");
            DB::statement("DROP TABLE IF EXISTS {$tableName}");
        }

        // Включаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->info('🔄 Запускаем миграции...');
        Artisan::call('migrate', ['--force' => true]);
    }



    /**
     * Отображает финальную сводку
     */
    private function displayFinalSummary(): void
    {
        $this->newLine();
        $this->info('📊 Финальная сводка:');
        $this->table(
            ['Операция', 'Статус'],
            [
                ['Удаление таблиц', '✅ Выполнено'],
                ['Запуск миграций', '✅ Выполнено'],
                ['База данных', '🆕 Готова к использованию'],
            ]
        );

        $this->newLine();
        $this->info('💡 Полезные команды:');
        $this->info('   • php artisan app:fill-demo-data - создать демо-данные');
        $this->info('   • php artisan app:clear-demo-data - очистить демо-данные');
        $this->info('   • php artisan app:reset-database --demo-only - очистить только демо-данные');
        $this->info('   • php artisan app:reset-database --seed - полная очистка + сидеры');
    }
}
