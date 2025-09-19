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
        return '🤖 Спасибо за сообщение!\n\n' .
               'К сожалению, я пока не умею понимать и обрабатывать текстовые сообщения.\n\n' .
               '📋 Доступные команды:\n' .
               '/start - приветствие\n' .
               '/help - справка\n' .
               '/status - статус бота\n\n' .
               '⚠️ Функционал обработки сообщений будет добавлен в ближайшее время.';
    }
}
