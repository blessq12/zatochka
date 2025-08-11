<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Contracts\TelegramServiceContract;
use App\Contracts\SMSServiceContract;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'clients:birthday-greetings';
    protected $description = 'Отправка поздравлений с днем рождения клиентам';

    public function handle(TelegramServiceContract $telegramService, SMSServiceContract $smsService)
    {
        $today = Carbon::today();

        $clients = Client::whereRaw('DATE_FORMAT(birth_date, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('birth_date')
            ->get();

        $this->info("Найдено {$clients->count()} клиентов с днем рождения сегодня");

        foreach ($clients as $client) {
            $message = "🎉 Дорогой {$client->full_name}! Поздравляем Вас с днем рождения! 🎂\n\n";
            $message .= "Желаем Вам крепкого здоровья и успехов во всех делах!\n";
            $message .= "В честь Вашего праздника дарим скидку 10% на все услуги в течение недели!\n\n";
            $message .= "С уважением, команда Заточка";

            // Отправляем в Telegram
            if ($client->telegram) {
                $telegramService->sendMessage($client->telegram, $message);
                $this->info("Поздравление отправлено в Telegram клиенту {$client->full_name}");
            }

            // Отправляем SMS
            if ($client->phone) {
                $smsMessage = "Поздравляем с ДР! Скидка 10% на все услуги неделю. Заточка";
                $smsService->sendMessage($client->phone, $smsMessage);
                $this->info("Поздравление отправлено SMS клиенту {$client->full_name}");
            }

            // Сохраняем уведомление
            $client->notifications()->create([
                'type' => 'birthday_greeting',
                'message_text' => $message,
                'sent_at' => now(),
            ]);
        }

        $this->info('Поздравления с днем рождения отправлены!');
    }
}
