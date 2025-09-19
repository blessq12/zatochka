<?php

namespace App\Infrastructure\Communication\Service;

use App\Domain\Communication\Service\TelegramWebhookServiceInterface;
use App\Domain\Communication\Service\TelegramServiceInterface;
use Illuminate\Support\Facades\Log;

class TelegramWebhookService implements TelegramWebhookServiceInterface
{
    private TelegramServiceInterface $telegramService;

    public function __construct(TelegramServiceInterface $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обработка webhook от Telegram
     */
    public function handleWebhook(array $data): array
    {
        try {
            $message = $this->parseMessage($data);

            if (!$message) {
                return ['status' => 'ignored', 'reason' => 'No message data'];
            }

            $chatId = $message['chat']['id'];
            $username = $message['from']['username'] ?? null;
            $text = $message['text'] ?? '';

            Log::info('Telegram webhook received', [
                'chat_id' => $chatId,
                'username' => $username,
                'text' => $text
            ]);

            // Обработка команды /start
            if ($text === '/start') {
                return $this->handleStartCommand($chatId, $username);
            }

            // Обработка текстовых сообщений (код верификации)
            if (!empty($text) && !str_starts_with($text, '/')) {
                return $this->handleTextMessage($chatId, $username, $text);
            }

            return ['status' => 'processed', 'action' => 'message_logged'];
        } catch (\Exception $e) {
            Log::error('Telegram webhook processing error', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Отправить код верификации
     */
    public function sendVerificationCode(string $chatId, string $code): bool
    {
        $message = "🔐 Код подтверждения: <b>{$code}</b>\n\nВведите этот код в приложении для завершения подключения Telegram.";

        return $this->telegramService->send($chatId, $message);
    }

    /**
     * Отправить приветственное сообщение
     */
    public function sendWelcomeMessage(string $chatId): bool
    {
        $message = "👋 Добро пожаловать!\n\nДля подключения Telegram к вашему аккаунту, пожалуйста, введите код подтверждения из приложения.";

        return $this->telegramService->send($chatId, $message);
    }

    /**
     * Парсинг сообщения из webhook
     */
    private function parseMessage(array $data): ?array
    {
        return $data['message'] ?? null;
    }

    /**
     * Обработка команды /start
     */
    private function handleStartCommand(string $chatId, ?string $username): array
    {
        $this->sendWelcomeMessage($chatId);

        return [
            'status' => 'processed',
            'action' => 'start_command',
            'chat_id' => $chatId,
            'username' => $username
        ];
    }

    /**
     * Обработка текстового сообщения
     */
    private function handleTextMessage(string $chatId, ?string $username, string $text): array
    {
        return [
            'status' => 'processed',
            'action' => 'verification_code',
            'chat_id' => $chatId,
            'username' => $username,
            'code' => $text
        ];
    }
}
