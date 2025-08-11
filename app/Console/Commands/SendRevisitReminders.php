<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Contracts\TelegramServiceContract;
use App\Contracts\SMSServiceContract;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendRevisitReminders extends Command
{
    protected $signature = 'clients:revisit-reminders';
    protected $description = 'Отправка напоминаний о повторном визите';

    public function handle(TelegramServiceContract $telegramService, SMSServiceContract $smsService)
    {
        // Клиенты, которые не заказывали более 30 дней
        $clients = Client::whereHas('orders', function ($query) {
            $query->where('created_at', '<=', Carbon::now()->subDays(30));
        })->whereDoesntHave('orders', function ($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        })->get();

        $this->info("Найдено {$clients->count()} клиентов для напоминания о повторном визите");

        foreach ($clients as $client) {
            $lastOrder = $client->orders()->latest()->first();
            $daysSinceLastOrder = $lastOrder ? Carbon::now()->diffInDays($lastOrder->created_at) : 30;

            $message = "🔧 Здравствуйте, {$client->full_name}!\n\n";
            $message .= "Прошло уже {$daysSinceLastOrder} дней с Вашего последнего визита.\n";
            $message .= "Возможно, Ваши инструменты снова нуждаются в заточке или обслуживании?\n\n";
            $message .= "💡 Специально для Вас - скидка 15% на все услуги при заказе в течение недели!\n\n";
            $message .= "Записаться можно по телефону или в Telegram.\n";
            $message .= "С уважением, команда Заточка";

            // Отправляем в Telegram
            if ($client->telegram) {
                $telegramService->sendMessage($client->telegram, $message);
                $this->info("Напоминание отправлено в Telegram клиенту {$client->full_name}");
            }

            // Отправляем SMS
            if ($client->phone) {
                $smsMessage = "Прошло {$daysSinceLastOrder} дней с последнего визита. Скидка 15% неделю. Заточка";
                $smsService->sendMessage($client->phone, $smsMessage);
                $this->info("Напоминание отправлено SMS клиенту {$client->full_name}");
            }

            // Сохраняем уведомление
            $client->notifications()->create([
                'type' => 'revisit_reminder',
                'message_text' => $message,
                'sent_at' => now(),
            ]);
        }

        $this->info('Напоминания о повторном визите отправлены!');
    }
}
