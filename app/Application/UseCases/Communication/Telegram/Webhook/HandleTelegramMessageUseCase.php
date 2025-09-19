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

        $response = $this->processMessage($message->getContent(), $chat);
        $this->telegramMessageService->sendMessage($chat->getChatId(), $response);

        return [
            'success' => true,
            'message' => 'Message processed and response sent',
            'chat_id' => $chat->getChatId(),
        ];
    }

    private function processMessage(string $messageText, TelegramChat $chat): string
    {
        return 'к сожалению, я не умею работать с текстовыми сообщениями 🤷🏻‍♂️';
    }
}
