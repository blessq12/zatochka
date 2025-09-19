<?php

namespace App\Application\UseCases\Communication;

use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Entity\TelegramMessage;
use App\Domain\Communication\Repository\TelegramChatRepository;
use App\Domain\Communication\Repository\TelegramMessageRepository;

abstract class BaseTelegramWebhookUseCase extends BaseCommunicationUseCase
{
    protected TelegramChatRepository $telegramChatRepository;
    protected TelegramMessageRepository $telegramMessageRepository;

    public function __construct()
    {
        parent::__construct();
        $this->telegramChatRepository = app(TelegramChatRepository::class);
        $this->telegramMessageRepository = app(TelegramMessageRepository::class);
    }

    protected function ensureChatExists(array $webhookData): TelegramChat
    {
        $chatId = $webhookData['message']['chat']['id'];
        $chatData = $webhookData['message']['chat'];

        return $this->telegramChatRepository->findOrCreate($chatId, $chatData);
    }

    protected function saveMessage(array $webhookData, TelegramChat $chat): TelegramMessage
    {
        $messageData = $webhookData['message'];

        return $this->telegramMessageRepository->create([
            'chat_id' => $chat->getId(),
            'client_id' => $chat->getClientId(),
            'content' => $messageData['text'] ?? '',
            'direction' => 'incoming',
            'sent_at' => now(),
        ]);
    }

    protected function validateSpecificData(): void
    {
        if (!isset($this->data['message'])) {
            throw new \InvalidArgumentException('Message data is required');
        }

        if (!isset($this->data['message']['chat']['id'])) {
            throw new \InvalidArgumentException('Chat ID is required');
        }
    }
}
