<?php

namespace App\Application\UseCases\Communication;

use Illuminate\Support\Facades\DB;

class DisconnectTelegramUseCase extends BaseCommunicationUseCase
{
    private ?int $clientId = null;
    private ?string $chatId = null;

    protected function validateSpecificData(): void
    {
        $this->clientId = $this->data['client_id'] ?? null;
        $this->chatId = $this->data['chat_id'] ?? null;

        if (empty($this->clientId)) {
            throw new \InvalidArgumentException('Client ID is required');
        }

        if (empty($this->chatId)) {
            throw new \InvalidArgumentException('Chat ID is required');
        }
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            // Находим чат
            $chat = $this->telegramChatRepository->findByChatId($this->chatId);

            if (!$chat) {
                throw new \InvalidArgumentException('Telegram chat not found');
            }

            // Проверяем что чат принадлежит клиенту
            if ($chat->clientId !== $this->clientId) {
                throw new \InvalidArgumentException('Chat does not belong to this client');
            }

            // Отключаем чат
            $this->telegramChatRepository->delete($chat->id);

            return [
                'success' => true,
                'chat_id' => $this->chatId,
                'disconnected_at' => now()
            ];
        });
    }
}
