<?php

namespace App\Infrastructure\Communication\Service;

use App\Domain\Communication\Service\TelegramServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService extends AbstractMessageService implements TelegramServiceInterface
{
    private string $botToken;
    private string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function send(string $chatId, string $message, array $options = []): bool
    {
        if (!$this->validateRecipient($chatId)) {
            Log::error('Invalid Telegram chat ID', ['chat_id' => $chatId]);
            return false;
        }

        $formattedMessage = $this->formatMessage($message);

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $formattedMessage,
                'parse_mode' => $options['parse_mode'] ?? 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'message' => $formattedMessage
                ]);
                return true;
            }

            Log::error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram API error', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправить сообщение с клавиатурой
     */
    public function sendWithKeyboard(string $chatId, string $message, array $buttons = []): bool
    {
        $keyboard = [
            'inline_keyboard' => $buttons
        ];

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram keyboard message error', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
