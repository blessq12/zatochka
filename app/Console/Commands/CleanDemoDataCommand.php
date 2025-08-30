<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Order;
use App\Models\Repair;
use App\Models\Review;
use App\Models\ClientBonus;
use App\Models\User;
use Illuminate\Console\Command;

class CleanDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-demo-data {--force : Принудительное выполнение без подтверждения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет демо данные, сохраняя базовые настройки';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Удалить демо данные (клиенты, заказы, отзывы)?')) {
            $this->info('❌ Операция отменена');
            return 0;
        }

        $this->info('🧹 Удаляем демо данные...');

        try {
            // Удаляем демо пользователей (кроме админа и демо)
            $deletedUsers = User::whereNotIn('email', ['admin@zatochka.org', 'demo@zatochka.org'])->delete();
            $this->info("🗑️  Удалено пользователей: {$deletedUsers}");

            // Удаляем все демо данные
            $deletedBonuses = ClientBonus::count();
            ClientBonus::truncate();
            $this->info("🎁 Удалено бонусов клиентов: {$deletedBonuses}");

            $deletedReviews = Review::count();
            Review::truncate();
            $this->info("⭐ Удалено отзывов: {$deletedReviews}");

            $deletedRepairs = Repair::count();
            Repair::truncate();
            $this->info("🔧 Удалено ремонтов: {$deletedRepairs}");

            $deletedOrders = Order::count();
            Order::truncate();
            $this->info("📋 Удалено заказов: {$deletedOrders}");

            $deletedClients = Client::count();
            Client::truncate();
            $this->info("👤 Удалено клиентов: {$deletedClients}");

            $this->info('✅ Демо данные успешно удалены!');
            $this->displaySummary();

        } catch (\Exception $e) {
            $this->error('❌ Ошибка при удалении демо данных: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Отображает сводку
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Оставшиеся данные:');
        $this->table(
            ['Тип', 'Количество'],
            [
                ['Пользователи', User::count()],
                ['Компании', \App\Models\Company::count()],
                ['Филиалы', \App\Models\Branch::count()],
                ['Клиенты', Client::count()],
                ['Заказы', Order::count()],
                ['Отзывы', Review::count()],
                ['Бонусы', ClientBonus::count()],
            ]
        );

        $this->newLine();
        $this->info('🔑 Сохраненные пользователи:');
        $users = User::all(['name', 'email', 'role']);
        $this->table(
            ['Имя', 'Email', 'Роль'],
            $users->map(fn($user) => [$user->name, $user->email, $user->role])->toArray()
        );
    }
}
