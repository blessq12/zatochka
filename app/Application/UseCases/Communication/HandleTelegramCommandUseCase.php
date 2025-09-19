<?php

namespace App\Application\UseCases\Communication;

use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Entity\TelegramMessage;

class HandleTelegramCommandUseCase extends BaseTelegramWebhookUseCase
{
    protected function validateSpecificData(): void
    {
        parent::validateSpecificData();

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

        // Обрабатываем команду
        $command = $this->data['message']['text'];
        $response = $this->processCommand($command, $chat);

        return [
            'success' => true,
            'message' => $response,
            'chat_id' => $chat->getChatId(),
        ];
    }

    private function processCommand(string $command, TelegramChat $chat): string
    {
        return match ($command) {
            '/start' => 'Добро пожаловать! Я бот для работы с заточкой.',
            '/help' => 'Доступные команды: /start, /help, /status',
            '/status' => 'Бот работает нормально.',
            default => 'Неизвестная команда. Используйте /help для списка команд.',
        };
    }
}
