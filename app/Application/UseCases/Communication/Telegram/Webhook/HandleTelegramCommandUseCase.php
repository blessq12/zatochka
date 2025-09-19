<?php

namespace App\Application\UseCases\Communication\Telegram\Webhook;

use App\Application\UseCases\Communication\BaseCommunicationUseCase;
use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Entity\TelegramMessage;

class HandleTelegramCommandUseCase extends BaseCommunicationUseCase
{
    protected function validateSpecificData(): void
    {
        $this->validateWebhookData();

        if (!isset($this->data['message']['text']) || !str_starts_with($this->data['message']['text'], '/')) {
            throw new \InvalidArgumentException('Command must start with /');
        }
    }

    public function execute(): array
    {
        // Обеспечиваем существование чата
        $chat = $this->ensureChatExists($this->data);

        // Сохраняем сообщение
        $message = $this->saveMessage($this->data, $chat);

        // Обрабатываем команду и отправляем ответ
        $command = $this->data['message']['text'];
        $response = $this->processCommand($command, $chat);

        // Отправляем ответ в чат
        $this->telegramMessageService->sendMessage($chat->getChatId(), $response);

        return [
            'success' => true,
            'message' => 'Command processed and response sent',
            'chat_id' => $chat->getChatId(),
        ];
    }

    private function processCommand(string $command, TelegramChat $chat): string
    {
        return match ($command) {
            '/start' => 'Бот работает.',
            '/help' => 'Доступные команды: /start, /help, /status',
            '/status' => 'Бот работает нормально.',
            default => 'я не умею обрабатывать кастомные команды 🤷🏻‍♂️',
        };
    }
}
