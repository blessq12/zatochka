<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class DiagnoseTelegramVerificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:telegram-verification {--client-id= : ID конкретного клиента}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Диагностика проблем с Telegram верификацией';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Диагностика Telegram верификации...');

        if ($clientId = $this->option('client-id')) {
            $this->diagnoseSpecificClient($clientId);
        } else {
            $this->diagnoseAllClients();
        }
    }

    /**
     * Диагностика конкретного клиента
     */
    private function diagnoseSpecificClient($clientId)
    {
        $client = Client::find($clientId);

        if (!$client) {
            $this->error("Клиент с ID {$clientId} не найден");
            return;
        }

        $this->diagnoseClient($client);
    }

    /**
     * Диагностика всех клиентов
     */
    private function diagnoseAllClients()
    {
        $clients = Client::whereNotNull('telegram')->get();

        $this->info("Найдено клиентов с Telegram: {$clients->count()}");

        $verifiedCount = 0;
        $unverifiedCount = 0;
        $problematicCount = 0;

        foreach ($clients as $client) {
            $this->line("\n--- Клиент ID: {$client->id} ---");
            $this->diagnoseClient($client);

            if ($client->isTelegramVerified()) {
                $verifiedCount++;
            } else {
                $unverifiedCount++;
            }

            // Проверяем на проблемные случаи
            if ($client->telegram && !$client->telegram_verified_at) {
                $problematicCount++;
            }
        }

        $this->info("\n📊 ИТОГОВАЯ СТАТИСТИКА:");
        $this->info("✅ Верифицированных: {$verifiedCount}");
        $this->info("❌ Не верифицированных: {$unverifiedCount}");
        $this->info("⚠️ Проблемных (есть Telegram, но нет верификации): {$problematicCount}");
    }

    /**
     * Диагностика одного клиента
     */
    private function diagnoseClient(Client $client)
    {
        $this->line("Имя: {$client->full_name}");
        $this->line("Телефон: {$client->phone}");
        $this->line("Telegram: " . ($client->telegram ?: 'не указан'));
        $this->line("telegram_verified_at: " . ($client->telegram_verified_at ?: 'null'));

        if ($client->telegram_verified_at) {
            $this->line("Формат даты: " . $client->telegram_verified_at->format('Y-m-d H:i:s'));
            $this->line("Часовой пояс: " . $client->telegram_verified_at->timezone->getName());
        }

        $isVerified = $client->isTelegramVerified();
        $this->line("isTelegramVerified(): " . ($isVerified ? 'true' : 'false'));

        if ($client->telegram && !$isVerified) {
            $this->warn("⚠️ ПРОБЛЕМА: Есть Telegram, но не верифицирован!");
        } elseif ($isVerified && !$client->telegram) {
            $this->warn("⚠️ ПРОБЛЕМА: Верифицирован, но нет Telegram!");
        } elseif ($isVerified && $client->telegram) {
            $this->info("✅ Все в порядке");
        } else {
            $this->line("ℹ️ Нет Telegram, не верифицирован");
        }
    }
}
