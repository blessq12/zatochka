<?php

namespace App\Http\Controllers\Api;

use App\Models\TelegramMessage;
use App\Http\Controllers\Controller;
use App\Models\TelegramChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function telegramCheckStatus(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Получаем обновление от Telegram
            $update = Telegram::getWebhookUpdate();

            Log::info('Telegram webhook received', [
                'update_id' => $update->getUpdateId(),
                'message' => $update->getMessage() ? $update->getMessage()->toArray() : null,
            ]);

            if (!$update->getMessage()) {
                return response()->json(['status' => 'ok']);
            }

            $message = $update->getMessage();
            $chat = $this->ensureChatExists($update);

            // Сохраняем сообщение
            $savedMessage = $this->saveMessage($update, $chat);

            // Обрабатываем сообщение
            if ($this->isCommand($message)) {
                $response = $this->processCommand($message->getText(), $chat);
            } else {
                $response = $this->processMessage($message->getText() ?? '', $chat);
            }

            // Отправляем ответ в чат
            Telegram::sendMessage([
                'chat_id' => $chat->chat_id,
                'text' => $response,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed and response sent',
                'chat_id' => $chat->chat_id,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Определяет является ли сообщение командой
     */
    private function isCommand($message): bool
    {
        // Проверяем entities на наличие bot_command
        $entities = $message->getEntities();
        if ($entities) {
            foreach ($entities as $entity) {
                if ($entity->getType() === 'bot_command') {
                    return true;
                }
            }
        }

        // Fallback: проверяем начинается ли текст с /
        $text = $message->getText();
        return $text && str_starts_with($text, '/');
    }

    public function telegramSendVerificationCode(Request $request): JsonResponse
    {
        try {
            // Получаем авторизованного клиента
            $client = Auth::guard('client')->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Клиент не авторизован',
                ], 401);
            }

            // Получаем telegram username из профиля клиента
            $telegramUsername = trim($client->telegram);

            if (!$telegramUsername) {
                return response()->json([
                    'success' => false,
                    'message' => 'Укажите Telegram username в настройках профиля',
                ], 400);
            }

            // Ищем чат клиента или по username
            $telegramChat = TelegramChat::where('client_id', $client->id)
                ->where('is_deleted', false)
                ->first();

            if (!$telegramChat) {
                $cleanUsername = ltrim($telegramUsername, '@');
                $telegramChat = TelegramChat::where('username', $cleanUsername)
                    ->where('is_deleted', false)
                    ->first();
            }

            if (!$telegramChat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Telegram чат не найден. Перейдите в бота @zatochka_bot и нажмите /start',
                ], 400);
            }

            // Генерируем код подтверждения
            $verificationCode = $this->generateVerificationCode();

            // Сохраняем код в кэш на 5 минут
            $this->storeVerificationCode($verificationCode, $telegramUsername, 5);

            // Форматируем сообщение
            $message = $this->formatVerificationMessage($verificationCode);

            // Отправляем сообщение в Telegram
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegramChat->chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Telegram verification code', [
                    'telegram_username' => $telegramUsername,
                    'chat_id' => $telegramChat->chat_id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось отправить код подтверждения. Попробуйте позже.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен в Telegram',
                'telegram_username' => $telegramUsername,
                'expires_in_minutes' => 5,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке кода: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function telegramVerifyCode(Request $request): JsonResponse
    {
        try {
            // Валидация входных данных
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверные данные',
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Получаем авторизованного клиента
            $client = Auth::guard('client')->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Клиент не авторизован',
                ], 401);
            }

            // Получаем telegram username из профиля клиента
            $telegramUsername = trim($client->telegram);

            if (!$telegramUsername) {
                return response()->json([
                    'success' => false,
                    'message' => 'Telegram username не найден для клиента',
                ], 400);
            }

            $providedCode = trim($request->input('code'));
            $storedCode = $this->getVerificationCode($telegramUsername);

            if (!$storedCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Код подтверждения не найден или истек. Запросите новый код.',
                ], 400);
            }

            if ($storedCode !== $providedCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный код подтверждения. Проверьте код и попробуйте снова.',
                ], 400);
            }

            // Обеспечиваем привязку чата к клиенту
            $this->ensureChatLinkedToClient($telegramUsername, $client->id);

            // Обновляем дату подтверждения Telegram у клиента
            $client = \App\Models\Client::find($client->id);
            $client->update([
                'telegram_verified_at' => now(),
            ]);

            // Очищаем код подтверждения
            $this->clearVerificationCode($telegramUsername);

            return response()->json([
                'success' => true,
                'message' => 'Telegram успешно подтвержден',
                'telegram_username' => $telegramUsername,
                'verified_at' => now()->toISOString(),
                'client' => $client->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при подтверждении кода: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function telegramCheckChatIsExists(Request $request): JsonResponse
    {
        try {

            // Получаем авторизованного клиента
            $client = Auth::guard('client')->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Клиент не авторизован',
                ], 401);
            }

            // Получаем telegram username из профиля клиента
            $telegramUsername = trim($client->telegram);

            if (!$telegramUsername) {
                return response()->json([
                    'success' => false,
                    'message' => 'Telegram username не указан в профиле',
                ], 400);
            }

            // Ищем чат по username
            $telegramChat = TelegramChat::where('username', $telegramUsername)
                ->where('is_deleted', false)
                ->first();

            if ($telegramChat) {
                // Связываем чат с клиентом
                $telegramChat->update([
                    'client_id' => $client->id,
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Telegram чат найден и привязан к аккаунту',
                    'chat_exists' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Telegram чат не найден',
                'chat_exists' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при проверке чата: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Генерирует код подтверждения
     */
    private function generateVerificationCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Сохраняет код подтверждения в кэш
     */
    private function storeVerificationCode(string $code, string $telegramUsername, int $minutes): void
    {
        $key = "telegram_verification_{$telegramUsername}";
        \Illuminate\Support\Facades\Cache::put($key, $code, now()->addMinutes($minutes));
    }

    /**
     * Форматирует сообщение с кодом подтверждения
     */
    private function formatVerificationMessage(string $code): string
    {
        return "🔐 <b>Код подтверждения</b>\n\n" .
            "Ваш код для подтверждения: <code>{$code}</code>\n\n" .
            "⚠️ Код действителен 5 минут\n" .
            "❌ Не передавайте код третьим лицам";
    }

    /**
     * Получает код подтверждения из кэша
     */
    private function getVerificationCode(string $telegramUsername): ?string
    {
        $key = "telegram_verification_{$telegramUsername}";
        return \Illuminate\Support\Facades\Cache::get($key);
    }

    /**
     * Очищает код подтверждения из кэша
     */
    private function clearVerificationCode(string $telegramUsername): void
    {
        $key = "telegram_verification_{$telegramUsername}";
        \Illuminate\Support\Facades\Cache::forget($key);
    }

    /**
     * Обеспечивает привязку чата к клиенту
     */
    private function ensureChatLinkedToClient(string $telegramUsername, int $clientId): void
    {
        // Проверяем, есть ли уже чат у клиента
        $existingChat = TelegramChat::where('client_id', $clientId)
            ->where('is_deleted', false)
            ->first();

        if ($existingChat) {
            return;
        }

        // Ищем чат по username
        $cleanUsername = ltrim($telegramUsername, '@');
        $telegramChat = TelegramChat::where('username', $cleanUsername)
            ->where('is_deleted', false)
            ->first();

        if ($telegramChat) {
            $telegramChat->update([
                'client_id' => $clientId,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Обеспечивает существование чата
     */
    private function ensureChatExists($update): TelegramChat
    {
        $message = $update->getMessage();
        $chat = $message->getChat();
        $chatId = $chat->getId();
        $username = $chat->getUsername();

        // Ищем существующий чат
        $telegramChat = TelegramChat::where('chat_id', $chatId)
            ->where('is_deleted', false)
            ->first();

        if (!$telegramChat) {
            // Создаем новый чат
            $telegramChat = TelegramChat::create([
                'chat_id' => $chatId,
                'username' => $username,
                'is_active' => true,
                'metadata' => [
                    'first_name' => $chat->getFirstName(),
                    'last_name' => $chat->getLastName(),
                    'type' => $chat->getType(),
                ],
            ]);
        }

        return $telegramChat;
    }

    /**
     * Сохраняет сообщение в базу
     */
    private function saveMessage($update, TelegramChat $chat): TelegramMessage
    {
        $message = $update->getMessage();

        return TelegramMessage::create([
            'chat_id' => $chat->id,
            'content' => $message->getText() ?? '',
            'direction' => 'incoming',
            'sent_at' => now(),
        ]);
    }

    /**
     * Обрабатывает команды
     */
    private function processCommand(string $command, TelegramChat $chat): string
    {
        return match ($command) {
            '/start' => 'Бот работает.',
            '/help' => 'Доступные команды: /start, /help, /status',
            '/status' => 'Бот работает нормально.',
            default => 'я не умею обрабатывать кастомные команды 🤷🏻‍♂️',
        };
    }

    /**
     * Обрабатывает обычные сообщения
     */
    private function processMessage(string $messageText, TelegramChat $chat): string
    {
        return 'к сожалению, я не умею работать с текстовыми сообщениями 🤷🏻‍♂️';
    }
}
