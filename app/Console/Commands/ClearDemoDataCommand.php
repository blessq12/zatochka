<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Order;
use App\Models\Repair;
use App\Models\ClientBonus;
use App\Models\BonusTransaction;
use App\Models\Review;
use App\Models\Notification;
use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-demo-data {--force : Force clearing without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очищает базу данных от демо-данных, оставляя только системные данные';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  ВНИМАНИЕ! Эта команда удалит ВСЕ демо-данные из базы данных. Продолжить?')) {
                $this->info('❌ Операция отменена.');
                return 0;
            }

            if (!$this->confirm('🤔 Вы уверены? Это действие НЕОБРАТИМО!')) {
                $this->info('❌ Операция отменена.');
                return 0;
            }
        }

        $this->info('🧹 Начинаем очистку демо-данных...');

        try {
            $this->clearDemoData();

            $this->info('✅ Демо-данные успешно удалены!');
            $this->displaySummary();
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при очистке демо-данных: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Очищает демо-данные из базы
     */
    private function clearDemoData(): void
    {
        $this->info('🗑️  Удаляем демо-данные...');

        // Удаляем в правильном порядке (сначала зависимые таблицы)

        // 1. Удаляем уведомления
        $this->info('📧 Удаляем уведомления...');
        $notificationsCount = Notification::count();
        Notification::query()->delete();
        $this->info("   Удалено уведомлений: {$notificationsCount}");

        // 2. Удаляем Telegram сообщения
        $this->info('💬 Удаляем Telegram сообщения...');
        $telegramMessagesCount = TelegramMessage::count();
        TelegramMessage::query()->delete();
        $this->info("   Удалено Telegram сообщений: {$telegramMessagesCount}");

        // 3. Удаляем Telegram чаты
        $this->info('📱 Удаляем Telegram чаты...');
        $telegramChatsCount = TelegramChat::count();
        TelegramChat::query()->delete();
        $this->info("   Удалено Telegram чатов: {$telegramChatsCount}");

        // 4. Удаляем отзывы
        $this->info('⭐ Удаляем отзывы...');
        $reviewsCount = Review::count();
        Review::query()->delete();
        $this->info("   Удалено отзывов: {$reviewsCount}");

        // 5. Удаляем бонусные транзакции
        $this->info('💰 Удаляем бонусные транзакции...');
        $bonusTransactionsCount = BonusTransaction::count();
        BonusTransaction::query()->delete();
        $this->info("   Удалено бонусных транзакций: {$bonusTransactionsCount}");

        // 6. Удаляем бонусы клиентов
        $this->info('🎁 Удаляем бонусы клиентов...');
        $clientBonusesCount = ClientBonus::count();
        ClientBonus::query()->delete();
        $this->info("   Удалено бонусов клиентов: {$clientBonusesCount}");

        // 7. Удаляем ремонты
        $this->info('🔧 Удаляем ремонты...');
        $repairsCount = Repair::count();
        Repair::query()->delete();
        $this->info("   Удалено ремонтов: {$repairsCount}");

        // 8. Удаляем заказы
        $this->info('📋 Удаляем заказы...');
        $ordersCount = Order::count();
        Order::query()->delete();
        $this->info("   Удалено заказов: {$ordersCount}");

        // 9. Удаляем клиентов
        $this->info('👤 Удаляем клиентов...');
        $clientsCount = Client::count();
        Client::query()->delete();
        $this->info("   Удалено клиентов: {$clientsCount}");

        // 10. Удаляем филиалы (кроме системных)
        $this->info('🏪 Удаляем филиалы...');
        $branchesCount = Branch::whereNotIn('code', ['BR1001', 'BR1002'])->count();
        Branch::whereNotIn('code', ['BR1001', 'BR1002'])->delete();
        $this->info("   Удалено филиалов: {$branchesCount}");

        // 11. Удаляем пользователей (кроме системных)
        $this->info('👥 Удаляем пользователей...');
        $usersCount = User::whereIn('email', [
            'anna.manager@zatochka-pro.ru',
            'sergey.manager@zatochka-pro.ru',
            'mikhail.master@zatochka-pro.ru',
            'alexey.master@zatochka-pro.ru'
        ])->count();
        User::whereIn('email', [
            'anna.manager@zatochka-pro.ru',
            'sergey.manager@zatochka-pro.ru',
            'mikhail.master@zatochka-pro.ru',
            'alexey.master@zatochka-pro.ru'
        ])->delete();
        $this->info("   Удалено пользователей: {$usersCount}");
        $this->info("   Сохранены системные пользователи: root@root.com");

        // 12. Удаляем компании (кроме системных)
        $this->info('🏢 Удаляем компании...');
        $companiesCount = DB::table('companies')->where('name', 'Заточка Про')->count();
        DB::table('companies')->where('name', 'Заточка Про')->delete();
        $this->info("   Удалено компаний: {$companiesCount}");

        // 13. Сбрасываем автоинкременты
        $this->info('🔄 Сбрасываем автоинкременты...');
        $this->resetAutoIncrements();
    }

    /**
     * Сбрасывает автоинкременты для таблиц
     */
    private function resetAutoIncrements(): void
    {
        $tables = [
            'notifications',
            'telegram_messages',
            'telegram_chats',
            'reviews',
            'bonus_transactions',
            'client_bonuses',
            'repairs',
            'orders',
            'clients',
            'branches',
            'users',
            'companies'
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
        }
    }

    /**
     * Отображает сводку оставшихся данных
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Сводка оставшихся данных:');
        $this->table(
            ['Тип', 'Количество'],
            [
                ['Компании', DB::table('companies')->count()],
                ['Филиалы', Branch::count()],
                ['Пользователи', User::count()],
                ['Клиенты', Client::count()],
                ['Заказы', Order::count()],
                ['Ремонты', Repair::count()],
                ['Бонусы клиентов', ClientBonus::count()],
                ['Бонусные транзакции', BonusTransaction::count()],
                ['Отзывы', Review::count()],
                ['Уведомления', Notification::count()],
                ['Telegram чаты', TelegramChat::count()],
                ['Telegram сообщения', TelegramMessage::count()],
            ]
        );

        $this->newLine();
        $this->info('✅ База данных очищена от демо-данных!');
        $this->info('💡 Для создания новых демо-данных используйте: php artisan app:fill-demo-data');
    }
}
