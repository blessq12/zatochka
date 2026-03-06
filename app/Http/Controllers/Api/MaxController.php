<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\MaxChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaxController extends Controller
{
    /**
     * Обработка вебхука от MAX
     * Документация: https://dev.max.ru/docs-api/objects/Update
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $update = $request->all();

            Log::info('MAX webhook received', ['update' => $update]);

            // Проверка secret (если настроен)
            $secret = config('services.max.webhook_secret');
            if ($secret && $request->header('X-Max-Bot-Api-Secret') !== $secret) {
                Log::warning('MAX webhook: invalid or missing secret');
                return response()->json(['ok' => false], 403);
            }

            $updateType = $update['update_type'] ?? null;

            if ($updateType !== 'message_created') {
                return response()->json([
                    'ok' => true,
                    'message' => 'Webhook received',
                    'update_type' => $updateType,
                ]);
            }

            $message = $update['message'] ?? null;
            if (!$message || !isset($message['sender'])) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Webhook received (no message/sender)',
                ]);
            }

            $sender = $message['sender'];
            $userId = $sender['user_id'] ?? null;
            $username = $sender['username'] ?? null;
            $firstName = $sender['first_name'] ?? null;
            $lastName = $sender['last_name'] ?? null;

            if (!$userId || ($sender['is_bot'] ?? false)) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Webhook received (skipped: no user_id or bot)',
                ]);
            }

            $body = $message['body'] ?? null;
            $text = $body['text'] ?? null;

            // Находим или создаём чат
            $maxChat = MaxChat::byUserId($userId)->first();

            if (!$maxChat) {
                $maxChat = MaxChat::create([
                    'user_id' => $userId,
                    'username' => $username ?? '',
                    'metadata' => [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ],
                    'is_active' => true,
                ]);
            } else {
                if ($username && ($maxChat->username !== $username || empty($maxChat->username))) {
                    $metadata = $maxChat->metadata ?? [];
                    $metadata['first_name'] = $firstName;
                    $metadata['last_name'] = $lastName;

                    $maxChat->update([
                        'username' => $username,
                        'metadata' => $metadata,
                    ]);
                }
            }

            $maxChat->refresh();

            if ($text) {
                $this->handleMessage($maxChat, $text, $userId);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Webhook received',
                'update_type' => $updateType,
            ]);
        } catch (\Exception $e) {
            Log::error('MAX webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Webhook error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Обработка входящих сообщений (заглушка для дальнейшей реализации)
     */
    private function handleMessage(MaxChat $chat, string $text, int $userId): void
    {
        $client = null;

        if ($chat->client_id) {
            $client = Client::find($chat->client_id);
        } elseif ($chat->username) {
            $client = Client::where('max_username', $chat->username)->first();
        }

        $command = trim(strtolower($text));

        if ($command === '/start') {
            $this->handleStartCommand($chat, $client, $chat->username);
            return;
        }

        // TODO: реализовать команды /account, /orders, /history, верификацию по коду
        Log::info('MAX message', [
            'user_id' => $userId,
            'text' => $text,
            'client_id' => $client?->id,
        ]);
    }

    private function handleStartCommand(MaxChat $chat, ?Client $client, ?string $username): void
    {
        $messenger = app('messenger.max');

        if (!$client) {
            $message = $username
                ? "❌ Ваш MAX username (@{$username}) не найден в нашей базе данных.\n\nДля использования бота:\n1. Зарегистрируйтесь на сайте\n2. Укажите ваш MAX username в личном кабинете\n3. Затем нажмите /start снова"
                : "❌ Ваш аккаунт не найден в нашей базе данных.\n\nДля использования бота:\n1. Зарегистрируйтесь на сайте\n2. Укажите ваш MAX username в личном кабинете\n3. Затем нажмите /start снова";
            $messenger->send((string) $chat->user_id, $message);
            return;
        }

        if ($client->max_verified_at) {
            $message = "✅ Ваш MAX уже подтвержден!\n\nТеперь вы будете получать уведомления о статусе ваших заказов автоматически.";
            $messenger->send((string) $chat->user_id, $message);
            return;
        }

        $message = $username
            ? "👋 Добро пожаловать!\n\nВаш MAX username (@{$username}) найден в базе данных, но еще не подтвержден.\n\nДля подтверждения:\n1. Перейдите в личный кабинет на сайте\n2. Нажмите 'Отправить код подтверждения'\n3. Введите полученный 6-значный код здесь"
            : "👋 Добро пожаловать!\n\nВаш аккаунт найден, но MAX еще не подтвержден.\n\nДля подтверждения:\n1. Перейдите в личный кабинет на сайте\n2. Укажите ваш MAX username и нажмите 'Отправить код подтверждения'\n3. Введите полученный 6-значный код здесь";
        $messenger->send((string) $chat->user_id, $message);
    }
}
