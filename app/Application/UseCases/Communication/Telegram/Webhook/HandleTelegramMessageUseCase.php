<?php

namespace App\Application\UseCases\Communication\Telegram\Webhook;

use App\Application\UseCases\Communication\BaseCommunicationUseCase;
use App\Domain\Communication\Entity\TelegramChat;

class HandleTelegramMessageUseCase extends BaseCommunicationUseCase
{
    protected function validateSpecificData(): void
    {
        $this->validateWebhookData();

        if (!isset($this->data['message']['text'])) {
            throw new \InvalidArgumentException('Message text is required');
        }
    }

    public function execute(): array
    {
        // Обеспечиваем существование чата
        $chat = $this->ensureChatExists($this->data);

        $message = $this->saveMessage($this->data, $chat);

        // Обрабатываем сообщение
        $response = $this->processMessage($message->getContent(), $chat);

        return [
            'success' => true,
            'message' => $response,
            'chat_id' => $chat->getChatId(),
        ];
    }

    private function processMessage(string $messageText, TelegramChat $chat): string
    {
        // TODO: Здесь будет логика обработки обычных сообщений
        // Например, поиск клиента, обработка заказов и т.д.

        return 'Сообщение получено: ' . $messageText;
    }
}
