<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Notification $notification
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            $client = $this->notification->client;

            // Проверяем, что клиент верифицирован в Telegram
            if (!$client->isTelegramVerified() || !$client->telegram) {
                Log::info('Client not verified in Telegram, skipping notification', [
                    'notification_id' => $this->notification->id,
                    'client_id' => $client->id
                ]);
                return;
            }

            // Формируем сообщение для Telegram
            $message = $this->formatTelegramMessage();

            // Отправляем сообщение
            $success = $telegramService->sendMessage($client->telegram, $message);

            if ($success) {
                Log::info('Notification sent to Telegram successfully', [
                    'notification_id' => $this->notification->id,
                    'client_id' => $client->id
                ]);
            } else {
                throw new \Exception('Failed to send notification to Telegram');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Format message for Telegram
     */
    private function formatTelegramMessage(): string
    {
        $notification = $this->notification;

        $message = "📢 <b>{$notification->title}</b>\n\n";
        $message .= $notification->message . "\n\n";

        if ($notification->order) {
            $message .= "📋 Заказ: <b>№{$notification->order->order_number}</b>\n";
        }

        $message .= "🕐 " . $notification->created_at->format('d.m.Y H:i');

        return $message;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [5, 15, 30]; // 5s, 15s, 30s delays
    }
}
