<?php

namespace App\Application\UseCases\Communication;

use App\Domain\Communication\Entity\TelegramChat;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConnectTelegramUseCase extends BaseCommunicationUseCase
{
    private ?string $username = null;
    private ?string $chatId = null;

    protected function validateSpecificData(): void
    {
        $this->username = $this->data['username'] ?? null;
        $this->chatId = $this->data['chat_id'] ?? null;

        if (empty($this->username)) {
            throw new \InvalidArgumentException('Username is required');
        }

        if (empty($this->chatId)) {
            throw new \InvalidArgumentException('Chat ID is required');
        }

        // Проверяем что клиент с таким username существует
        $client = Client::where('telegram', $this->username)->first();
        if (!$client) {
            throw new \InvalidArgumentException('Client with this Telegram username not found');
        }

        // Проверяем что чат еще не подключен
        $existingChat = $this->telegramChatRepository->findByChatId($this->chatId);
        if ($existingChat) {
            throw new \InvalidArgumentException('This Telegram chat is already connected');
        }
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            // Находим клиента
            $client = Client::where('telegram', $this->username)->first();

            // Создаем Domain Entity
            $telegramChat = new TelegramChat(
                id: 0, // Новый объект
                clientId: $client->id,
                username: $this->username,
                chatId: $this->chatId,
                isActive: false, // Пока не подтвержден
                metadata: [
                    'connected_at' => now(),
                    'verification_pending' => true
                ]
            );

            // Сохраняем через Repository
            $this->telegramChatRepository->save($telegramChat);

            // Генерируем код верификации
            $verificationCode = Str::random(6);

            // Сохраняем код в сессии на 10 минут
            session([
                'telegram_verification_code' => $verificationCode,
                'telegram_verification_chat_id' => $this->chatId,
                'telegram_verification_expires' => now()->addMinutes(10)
            ]);

            // Отправляем код в Telegram
            $this->telegramWebhookService->sendVerificationCode($this->chatId, $verificationCode);

            return [
                'success' => true,
                'chat_id' => $this->chatId,
                'verification_code' => $verificationCode,
                'expires_at' => now()->addMinutes(10)
            ];
        });
    }
}
