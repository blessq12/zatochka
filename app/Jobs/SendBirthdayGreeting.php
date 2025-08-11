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

class SendBirthdayGreeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    public function __construct(
        protected Client $client
    ) {}

    public function handle(TelegramServiceContract $telegramService, SMSServiceContract $smsService): void
    {
        $message = "🎉 Дорогой {$this->client->full_name}! Поздравляем Вас с днем рождения! 🎂\n\n";
        $message .= "Желаем Вам крепкого здоровья и успехов во всех делах!\n";
        $message .= "В честь Вашего праздника дарим скидку 10% на все услуги в течение недели!\n\n";
        $message .= "С уважением, команда Заточка";

        // Отправляем в Telegram
        if ($this->client->telegram) {
            $telegramService->sendMessage($this->client->telegram, $message);
        }

        // Отправляем SMS
        if ($this->client->phone) {
            $smsMessage = "Поздравляем с ДР! Скидка 10% на все услуги неделю. Заточка";
            $smsService->sendMessage($this->client->phone, $smsMessage);
        }

        // Сохраняем уведомление
        $this->client->notifications()->create([
            'type' => 'birthday_greeting',
            'message_text' => $message,
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        // Логируем ошибку
        \Log::error('Ошибка отправки поздравления с ДР', [
            'client_id' => $this->client->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
