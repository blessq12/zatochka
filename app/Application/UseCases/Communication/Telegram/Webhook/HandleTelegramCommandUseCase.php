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
            '/start' => '🤖 Добро пожаловать в бот Заточка!\n\nПока что я умею только отвечать на базовые команды. Функционал будет расширен в ближайшее время.',
            '/help' => '📋 Доступные команды:\n\n/start - приветствие\n/help - эта справка\n/status - статус бота\n\n⚠️ Обработка кастомных команд пока не реализована.',
            '/status' => '✅ Бот работает нормально!\n\n🔧 Функционал в разработке...',
            default => '❌ Неизвестная команда.\n\nИспользуйте /help для списка доступных команд.\n\n⚠️ Обработка кастомных команд пока не реализована.',
        };
    }
}
