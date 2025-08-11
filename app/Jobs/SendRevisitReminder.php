<?php

namespace App\Jobs;

use App\Models\Client;
use App\Contracts\TelegramServiceContract;
use App\Contracts\SMSServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SendRevisitReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    public function __construct(
        protected Client $client
    ) {}

    public function handle(TelegramServiceContract $telegramService, SMSServiceContract $smsService): void
    {
        $lastOrder = $this->client->orders()->latest()->first();
        $daysSinceLastOrder = $lastOrder ? Carbon::now()->diffInDays($lastOrder->created_at) : 30;

        $message = "🔧 Здравствуйте, {$this->client->full_name}!\n\n";
        $message .= "Прошло уже {$daysSinceLastOrder} дней с Вашего последнего визита.\n";
        $message .= "Возможно, Ваши инструменты снова нуждаются в заточке или обслуживании?\n\n";
        $message .= "💡 Специально для Вас - скидка 15% на все услуги при заказе в течение недели!\n\n";
        $message .= "Записаться можно по телефону или в Telegram.\n";
        $message .= "С уважением, команда Заточка";

        // Отправляем в Telegram
        if ($this->client->telegram) {
            $telegramService->sendMessage($this->client->telegram, $message);
        }

        // Отправляем SMS
        if ($this->client->phone) {
            $smsMessage = "Прошло {$daysSinceLastOrder} дней с последнего визита. Скидка 15% неделю. Заточка";
            $smsService->sendMessage($this->client->phone, $smsMessage);
        }

        // Сохраняем уведомление
        $this->client->notifications()->create([
            'type' => 'revisit_reminder',
            'message_text' => $message,
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        // Логируем ошибку
        \Log::error('Ошибка отправки напоминания о повторном визите', [
            'client_id' => $this->client->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
