<?php

namespace App\Application\UseCases\Communication;

use App\Domain\Communication\Entity\TelegramChat;
use Illuminate\Support\Facades\DB;

class VerifyTelegramConnectionUseCase extends BaseCommunicationUseCase
{
    private ?string $code = null;
    private ?string $chatId = null;

    protected function validateSpecificData(): void
    {
        $this->code = $this->data['code'] ?? null;
        $this->chatId = $this->data['chat_id'] ?? null;

        if (empty($this->code)) {
            throw new \InvalidArgumentException('Verification code is required');
        }

        if (empty($this->chatId)) {
            throw new \InvalidArgumentException('Chat ID is required');
        }

        // Проверяем код в сессии
        $sessionCode = session('telegram_verification_code');
        $sessionChatId = session('telegram_verification_chat_id');
        $expiresAt = session('telegram_verification_expires');

        if (!$sessionCode || !$sessionChatId || !$expiresAt) {
            throw new \InvalidArgumentException('No verification session found');
        }

        if ($sessionChatId !== $this->chatId) {
            throw new \InvalidArgumentException('Invalid chat ID');
        }

        if ($sessionCode !== $this->code) {
            throw new \InvalidArgumentException('Invalid verification code');
        }

        if (now()->isAfter($expiresAt)) {
            throw new \InvalidArgumentException('Verification code has expired');
        }
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            // Находим чат через Repository
            $chat = $this->telegramChatRepository->findByChatId($this->chatId);

            if (!$chat) {
                throw new \InvalidArgumentException('Telegram chat not found');
            }

            // Активируем чат через Domain Entity
            $activatedChat = $chat->activate();

            // Сохраняем через Repository
            $this->telegramChatRepository->save($activatedChat);

            // Очищаем сессию
            session()->forget([
                'telegram_verification_code',
                'telegram_verification_chat_id',
                'telegram_verification_expires'
            ]);

            return [
                'success' => true,
                'chat_id' => $this->chatId,
                'client_id' => $activatedChat->clientId,
                'verified_at' => now()
            ];
        });
    }
}
