<?php

namespace App\Listeners\Bonus;

use App\Events\Bonus\BonusSpent;
use App\Services\TelegramService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBonusSpentNotification implements ShouldQueue
{
    protected TelegramService $telegramService;
    protected NotificationService $notificationService;

    public function __construct(TelegramService $telegramService, NotificationService $notificationService)
    {
        $this->telegramService = $telegramService;
        $this->notificationService = $notificationService;
    }

    public function handle(BonusSpent $event): void
    {
        $client = $event->client;
        $amount = $event->amount;
        $reason = $event->reason;
        $order = $event->order;

        Log::info('Bonus spent notification', [
            'client_id' => $client->id,
            'amount' => $amount,
            'reason' => $reason,
            'order_id' => $order?->id,
        ]);

        // Создаем уведомление в системе
        $message = $this->buildMessage($client, $amount, $reason, $order);

        $client->notifications()->create([
            'type' => 'bonus_spent',
            'message_text' => $message,
            'sent_at' => now(),
        ]);

        // Отправляем в Telegram если клиент подключен
        if ($client->telegram && $client->isTelegramVerified()) {
            $this->telegramService->sendBonusSpentNotification(
                $this->telegramService->getClientChatId($client->id),
                [
                    'client_name' => $client->full_name,
                    'amount' => $amount,
                    'reason' => $reason,
                    'order_number' => $order?->order_number,
                ]
            );
        }
    }

    protected function buildMessage($client, $amount, $reason, $order): string
    {
        $message = "💳 Здравствуйте, {$client->full_name}!\n\n";

        switch ($reason) {
            case 'order':
                $message .= "По заказу {$order->order_number} списано " . number_format($amount, 0) . " бонусных рублей.\n";
                $message .= "Спасибо за использование бонусной программы!\n";
                break;

            case 'expired':
                $message .= "Списано " . number_format($amount, 0) . " просроченных бонусных рублей.\n";
                $message .= "Не забывайте использовать бонусы в течение 3 месяцев!\n";
                break;

            default:
                $message .= "Списано " . number_format($amount, 0) . " бонусных рублей.\n";
                $message .= "Причина: {$reason}\n";
        }

        $message .= "\nС уважением, команда Заточка";

        return $message;
    }
}
