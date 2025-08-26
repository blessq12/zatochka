<?php

namespace App\Listeners\Bonus;

use App\Events\Bonus\BonusExpiring;
use App\Services\TelegramService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBonusExpiringNotification implements ShouldQueue
{
    protected TelegramService $telegramService;
    protected NotificationService $notificationService;

    public function __construct(TelegramService $telegramService, NotificationService $notificationService)
    {
        $this->telegramService = $telegramService;
        $this->notificationService = $notificationService;
    }

    public function handle(BonusExpiring $event): void
    {
        $client = $event->client;
        $balance = $event->balance;
        $daysLeft = $event->daysLeft;

        Log::info('Bonus expiring notification', [
            'client_id' => $client->id,
            'balance' => $balance,
            'days_left' => $daysLeft,
        ]);

        // Создаем уведомление в системе
        $message = $this->buildMessage($client, $balance, $daysLeft);

        $client->notifications()->create([
            'type' => 'bonus_expiring',
            'message_text' => $message,
            'sent_at' => now(),
        ]);

        // Отправляем в Telegram если клиент подключен
        if ($client->telegram && $client->isTelegramVerified()) {
            $this->telegramService->sendBonusExpiringNotification(
                $this->telegramService->getClientChatId($client->id),
                [
                    'client_name' => $client->full_name,
                    'balance' => $balance,
                    'days_left' => $daysLeft,
                ]
            );
        }
    }

    protected function buildMessage($client, $balance, $daysLeft): string
    {
        $message = "⏰ Здравствуйте, {$client->full_name}!\n\n";

        $message .= "У Вас есть " . number_format($balance, 0) . " бонусных рублей, которые истекают через {$daysLeft} " . $this->getDaysWord($daysLeft) . ".\n\n";
        $message .= "Не упустите возможность использовать их при следующем заказе!\n";
        $message .= "Бонусы можно использовать для оплаты до 50% стоимости заказа.\n\n";
        $message .= "С уважением, команда Заточка";

        return $message;
    }

    protected function getDaysWord(int $days): string
    {
        if ($days === 1) {
            return 'день';
        } elseif ($days >= 2 && $days <= 4) {
            return 'дня';
        } else {
            return 'дней';
        }
    }
}
