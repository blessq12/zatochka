<?php

namespace App\Infrastructure\Communication\Service;

use App\Domain\Communication\Service\TelegramMessageServiceInterface;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramMessageServiceImpl implements TelegramMessageServiceInterface
{
    public function __construct()
    {
        // Telegram facade уже настроен через конфигурацию
    }

    public function sendMessage(int $chatId, string $text, ?array $options = null): array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options ?? []);

            $response = Telegram::sendMessage($params);

            Log::info('Telegram message sent', [
                'chat_id' => $chatId,
                'message_id' => $response->getMessageId(),
                'text' => $text,
            ]);

            return [
                'success' => true,
                'message_id' => $response->getMessageId(),
                'chat_id' => $chatId,
                'text' => $text,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'text' => $text,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ];
        }
    }

    public function sendMessageWithKeyboard(int $chatId, string $text, array $keyboard): array
    {
        $options = [
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ];

        return $this->sendMessage($chatId, $text, $options);
    }

    public function sendMessageWithInlineKeyboard(int $chatId, string $text, array $inlineKeyboard): array
    {
        $options = [
            'reply_markup' => json_encode([
                'inline_keyboard' => $inlineKeyboard,
            ]),
        ];

        return $this->sendMessage($chatId, $text, $options);
    }

    public function editMessage(int $chatId, int $messageId, string $text, ?array $options = null): array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options ?? []);

            $response = Telegram::editMessageText($params);

            Log::info('Telegram message edited', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
            ]);

            return [
                'success' => true,
                'message_id' => $response->getMessageId(),
                'chat_id' => $chatId,
                'text' => $text,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to edit Telegram message', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ];
        }
    }

    public function deleteMessage(int $chatId, int $messageId): array
    {
        try {
            $response = Telegram::deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            Log::info('Telegram message deleted', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            return [
                'success' => true,
                'message_id' => $messageId,
                'chat_id' => $chatId,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to delete Telegram message', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ];
        }
    }

    public function sendPhoto(int $chatId, string $photo, ?string $caption = null, ?array $options = null): array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'photo' => $photo,
                'parse_mode' => 'HTML',
            ], $options ?? []);

            if ($caption) {
                $params['caption'] = $caption;
            }

            $response = Telegram::sendPhoto($params);

            Log::info('Telegram photo sent', [
                'chat_id' => $chatId,
                'message_id' => $response->getMessageId(),
                'photo' => $photo,
            ]);

            return [
                'success' => true,
                'message_id' => $response->getMessageId(),
                'chat_id' => $chatId,
                'photo' => $photo,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to send Telegram photo', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'photo' => $photo,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ];
        }
    }

    public function sendDocument(int $chatId, string $document, ?string $caption = null, ?array $options = null): array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'document' => $document,
                'parse_mode' => 'HTML',
            ], $options ?? []);

            if ($caption) {
                $params['caption'] = $caption;
            }

            $response = Telegram::sendDocument($params);

            Log::info('Telegram document sent', [
                'chat_id' => $chatId,
                'message_id' => $response->getMessageId(),
                'document' => $document,
            ]);

            return [
                'success' => true,
                'message_id' => $response->getMessageId(),
                'chat_id' => $chatId,
                'document' => $document,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to send Telegram document', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'document' => $document,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ];
        }
    }

    public function getChat(int $chatId): array
    {
        try {
            $response = Telegram::getChat(['chat_id' => $chatId]);

            return [
                'success' => true,
                'chat' => $response->toArray(),
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to get Telegram chat info', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ];
        }
    }

    public function getMe(): array
    {
        try {
            $response = Telegram::getMe();

            return [
                'success' => true,
                'bot' => $response->toArray(),
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to get Telegram bot info', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
