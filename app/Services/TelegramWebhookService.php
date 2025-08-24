<?php

namespace App\Services;

use App\Contracts\TelegramWebhookServiceContract;
use App\Models\Client;
use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\Message;

class TelegramWebhookService implements TelegramWebhookServiceContract
{
    protected Api $telegram;
    protected string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->telegram = new Api($this->botToken);
    }

    /**
     * Обработать входящий webhook от Telegram
     */
    public function handleWebhook(array $data): void
    {
        try {
            $update = new Update($data);

            if ($update->has('message')) {
                $this->handleMessage($update->getMessage());
            } elseif ($update->has('callback_query')) {
                $this->handleCallbackQuery($update->getCallbackQuery());
            }
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Обработать сообщение
     */
    protected function handleMessage(Message $message): void
    {
        $chatId = $message->getChat()->getId();
        $username = $message->getFrom()->getUsername();
        $firstName = $message->getFrom()->getFirstName();
        $lastName = $message->getFrom()->getLastName();
        $text = $message->getText();
        $messageId = $message->getMessageId();

        // Находим или создаем чат
        $chat = TelegramChat::findByChatId($chatId);
        if (!$chat && $username) {
            $chat = TelegramChat::createOrUpdate([
                'username' => $username,
                'chat_id' => $chatId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'is_active' => true,
                'last_activity_at' => now(),
            ]);
        }

        // Сохраняем входящее сообщение (только для обычных сообщений, не команд)
        if ($chat && !str_starts_with($text, '/')) {
            $client = Client::where('telegram', $username)->first();

            TelegramMessage::createIncoming([
                'telegram_chat_id' => $chat->id,
                'client_id' => $client?->id,
                'message_id' => $messageId,
                'type' => 'text',
                'content' => $text,
                'metadata' => [
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ],
            ]);

            // Обновляем время последней активности чата
            $chat->updateLastActivity();
        }

        // Обрабатываем команды
        if (str_starts_with($text, '/')) {
            $this->handleCommand($chatId, $username, $firstName, $lastName, $text, $messageId);
            return;
        }

        // Обрабатываем обычные сообщения
        $this->handleTextMessage($chatId, $username, $text);
    }

    /**
     * Обработать callback query
     */
    protected function handleCallbackQuery($callbackQuery): void
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $data = $callbackQuery->getData();

        // Обрабатываем callback данные
        $this->handleCallbackData($chatId, $data);

        // Отвечаем на callback query
        $this->telegram->answerCallbackQuery([
            'callback_query_id' => $callbackQuery->getId()
        ]);
    }

    /**
     * Обработать команду
     */
    protected function handleCommand(int $chatId, ?string $username, ?string $firstName, ?string $lastName, string $text, int $messageId = 0): void
    {
        $command = strtolower(trim($text));

        switch ($command) {
            case '/start':
                $this->handleStartCommand($chatId, $username, $firstName, $lastName, $messageId);
                break;
            case '/help':
                $this->handleHelpCommand($chatId, $messageId);
                break;
            case '/status':
                $this->handleStatusCommand($chatId, $username, $messageId);
                break;
            case '/verify':
                $this->handleVerifyCommand($chatId, $username, $messageId);
                break;
            default:
                $this->handleUnknownCommand($chatId, $messageId);
                break;
        }
    }

    /**
     * Обработать текстовое сообщение
     */
    protected function handleTextMessage(int $chatId, ?string $username, string $text): void
    {
        if (!$username) {
            $this->sendMessage($chatId, "❌ Для работы с ботом необходимо указать username в Telegram");
            return;
        }

        // Проверяем состояние пользователя
        $stateKey = "telegram_state_{$username}";
        $state = Cache::get($stateKey);

        if (!$state) {
            $this->handleUnknownCommand($chatId);
            return;
        }

        switch ($state) {
            case 'waiting_phone':
                $this->handlePhoneNumber($chatId, $username, $text);
                break;
            case 'waiting_code':
                $this->handleVerificationCode($chatId, $username, $text);
                break;
            default:
                $this->handleUnknownCommand($chatId);
                break;
        }
    }

    /**
     * Обработать callback данные
     */
    protected function handleCallbackData(int $chatId, string $data): void
    {
        // Здесь можно обрабатывать нажатия на inline кнопки
    }

    /**
     * Обработать команду /start
     */
    protected function handleStartCommand(int $chatId, ?string $username, ?string $firstName, ?string $lastName, int $messageId = 0): void
    {
        if (!$username) {
            $this->sendMessage($chatId, "❌ Для работы с ботом необходимо указать username в Telegram");
            return;
        }

        // Сохраняем или обновляем информацию о чате
        $chat = \App\Models\TelegramChat::createOrUpdate([
            'username' => $username,
            'chat_id' => $chatId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_active' => true,
            'last_activity_at' => now(),
        ]);

        // Сохраняем входящее сообщение команды /start
        $client = Client::where('telegram', $username)->first();
        TelegramMessage::createIncoming([
            'telegram_chat_id' => $chat->id,
            'client_id' => $client?->id,
            'message_id' => $messageId,
            'type' => 'command',
            'content' => '/start',
            'metadata' => [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'command' => 'start',
            ],
        ]);

        $message = "👋 <b>Добро пожаловать в бот Заточка ТСК!</b>\n\n";
        $message .= "🔧 Мы предоставляем услуги заточки и ремонта инструментов.\n\n";
        $message .= "📱 Для получения уведомлений о заказах необходимо привязать ваш аккаунт.\n\n";
        $message .= "Отправьте ваш номер телефона в формате: <code>+79991234567</code>";

        // Устанавливаем состояние ожидания номера телефона
        Cache::put("telegram_state_{$username}", 'waiting_phone', 300); // 5 минут

        $this->sendMessage($chatId, $message);
    }

    /**
     * Обработать команду /help
     */
    protected function handleHelpCommand(int $chatId, int $messageId = 0): void
    {
        // Находим чат
        $chat = TelegramChat::findByChatId($chatId);
        if ($chat) {
            // Сохраняем входящее сообщение команды /help
            $client = $chat->client;
            TelegramMessage::createIncoming([
                'telegram_chat_id' => $chat->id,
                'client_id' => $client?->id,
                'message_id' => $messageId,
                'type' => 'command',
                'content' => '/help',
                'metadata' => [
                    'command' => 'help',
                ],
            ]);
        }

        $message = "🤖 <b>Команды бота Заточка ТСК:</b>\n\n";
        $message .= "/start - Начать работу с ботом\n";
        $message .= "/help - Показать эту справку\n";
        $message .= "/status - Проверить статус аккаунта\n";
        $message .= "/verify - Пройти верификацию\n\n";
        $message .= "📞 <b>Контакты:</b>\n";
        $message .= "Телефон: +7 (999) 123-45-67\n";
        $message .= "Email: info@zatochka.tsk\n";
        $message .= "Сайт: https://zatochka.tsk";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Обработать команду /status
     */
    protected function handleStatusCommand(int $chatId, ?string $username, int $messageId = 0): void
    {
        if (!$username) {
            $this->sendMessage($chatId, "❌ Username не указан");
            return;
        }

        // Находим чат и сохраняем входящее сообщение команды /status
        $chat = TelegramChat::findByChatId($chatId);
        if ($chat) {
            $client = $chat->client;
            TelegramMessage::createIncoming([
                'telegram_chat_id' => $chat->id,
                'client_id' => $client?->id,
                'message_id' => $messageId,
                'type' => 'command',
                'content' => '/status',
                'metadata' => [
                    'username' => $username,
                    'command' => 'status',
                ],
            ]);
        }

        $client = Client::where('telegram', $username)->first();

        if (!$client) {
            $message = "❌ Ваш аккаунт не привязан к боту.\n\n";
            $message .= "Используйте команду /start для привязки аккаунта.";
        } else {
            $message = "✅ <b>Статус вашего аккаунта:</b>\n\n";
            $message .= "👤 Имя: <b>{$client->full_name}</b>\n";
            $message .= "📱 Телефон: <b>{$client->phone}</b>\n";
            $message .= "🔗 Telegram: <b>@{$client->telegram}</b>\n";
            $message .= "✅ Верификация: " . ($client->isTelegramVerified() ? "Подтверждена" : "Не подтверждена") . "\n\n";

            if ($client->isTelegramVerified()) {
                $message .= "🎉 Вы получаете уведомления о заказах!";
            } else {
                $message .= "⚠️ Для получения уведомлений используйте команду /verify";
            }
        }

        $this->sendMessage($chatId, $message);
    }

    /**
     * Обработать команду /verify
     */
    protected function handleVerifyCommand(int $chatId, ?string $username, int $messageId = 0): void
    {
        if (!$username) {
            $this->sendMessage($chatId, "❌ Username не указан");
            return;
        }

        // Находим чат и сохраняем входящее сообщение команды /verify
        $chat = TelegramChat::findByChatId($chatId);
        if ($chat) {
            $client = $chat->client;
            TelegramMessage::createIncoming([
                'telegram_chat_id' => $chat->id,
                'client_id' => $client?->id,
                'message_id' => $messageId,
                'type' => 'command',
                'content' => '/verify',
                'metadata' => [
                    'username' => $username,
                    'command' => 'verify',
                ],
            ]);
        }

        $client = Client::where('telegram', $username)->first();

        if (!$client) {
            $message = "❌ Ваш аккаунт не привязан к боту.\n\n";
            $message .= "Используйте команду /start для привязки аккаунта.";
            $this->sendMessage($chatId, $message);
            return;
        }

        if ($client->isTelegramVerified()) {
            $message = "✅ Ваш аккаунт уже верифицирован!\n\n";
            $message .= "Вы получаете уведомления о заказах.";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Генерируем код верификации
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Сохраняем код в кеше
        $cacheKey = "telegram_verification_{$client->phone}";
        Cache::put($cacheKey, $verificationCode, 600); // 10 минут

        // Устанавливаем состояние ожидания кода
        Cache::put("telegram_state_{$username}", 'waiting_code', 600);

        $message = "🔐 <b>Верификация аккаунта</b>\n\n";
        $message .= "Код верификации отправлен на ваш номер телефона.\n\n";
        $message .= "Введите код в формате: <code>{$verificationCode}</code>";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Обработать ввод кода верификации
     */
    protected function handleVerificationCode(int $chatId, string $username, string $code): void
    {
        $client = Client::where('telegram', $username)->first();

        if (!$client) {
            $this->sendMessage($chatId, "❌ Аккаунт не найден");
            return;
        }

        $cacheKey = "telegram_verification_{$client->phone}";
        $storedCode = Cache::get($cacheKey);

        if (!$storedCode) {
            $message = "❌ Код верификации истек.\n\n";
            $message .= "Используйте команду /verify для получения нового кода.";
            $this->sendMessage($chatId, $message);
            return;
        }

        if ($storedCode !== $code) {
            $message = "❌ Неверный код верификации.\n\n";
            $message .= "Проверьте код и попробуйте снова.";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Верифицируем клиента
        $client->markTelegramAsVerified();

        // Очищаем кеш
        Cache::forget($cacheKey);
        Cache::forget("telegram_state_{$username}");

        $message = "✅ <b>Аккаунт успешно верифицирован!</b>\n\n";
        $message .= "Теперь вы будете получать уведомления о:\n";
        $message .= "• Статусе заказов\n";
        $message .= "• Готовности к выдаче\n";
        $message .= "• Специальных предложениях\n\n";
        $message .= "Спасибо за доверие! 🎉";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Обработать ввод номера телефона
     */
    protected function handlePhoneNumber(int $chatId, string $username, string $phone): void
    {
        // Очищаем номер телефона от лишних символов
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Проверяем формат номера
        if (!preg_match('/^\+7[0-9]{10}$/', $phone)) {
            $message = "❌ Неверный формат номера телефона.\n\n";
            $message .= "Используйте формат: <code>+79991234567</code>";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Ищем клиента по номеру телефона
        $client = Client::where('phone', $phone)->first();

        if (!$client) {
            $message = "❌ Клиент с таким номером телефона не найден.\n\n";
            $message .= "Убедитесь, что вы зарегистрированы на сайте.";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Обновляем Telegram аккаунт клиента
        $client->update(['telegram' => $username]);

        // Очищаем состояние
        Cache::forget("telegram_state_{$username}");

        $message = "✅ <b>Аккаунт успешно привязан!</b>\n\n";
        $message .= "👤 Имя: <b>{$client->full_name}</b>\n";
        $message .= "📱 Телефон: <b>{$client->phone}</b>\n";
        $message .= "🔗 Telegram: <b>@{$username}</b>\n\n";

        if ($client->isTelegramVerified()) {
            $message .= "🎉 Вы уже верифицированы и получаете уведомления!";
        } else {
            $message .= "Для получения уведомлений используйте команду /verify";
        }

        $this->sendMessage($chatId, $message);
    }

    /**
     * Обработать неизвестную команду
     */
    protected function handleUnknownCommand(int $chatId, int $messageId = 0): void
    {
        // Находим чат и сохраняем входящее сообщение неизвестной команды
        $chat = TelegramChat::findByChatId($chatId);
        if ($chat) {
            $client = $chat->client;
            TelegramMessage::createIncoming([
                'telegram_chat_id' => $chat->id,
                'client_id' => $client?->id,
                'message_id' => $messageId,
                'type' => 'command',
                'content' => 'unknown_command',
                'metadata' => [
                    'command' => 'unknown',
                ],
            ]);
        }

        $message = "❓ Неизвестная команда.\n\n";
        $message .= "Используйте /help для просмотра доступных команд.";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Отправить сообщение
     */
    protected function sendMessage(int $chatId, string $text): void
    {
        try {
            $response = $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);

            // Сохраняем исходящее сообщение
            $chat = TelegramChat::findByChatId($chatId);
            if ($chat) {
                $client = $chat->client;

                TelegramMessage::createOutgoing([
                    'telegram_chat_id' => $chat->id,
                    'client_id' => $client?->id,
                    'message_id' => $response->getMessageId(),
                    'type' => 'text',
                    'content' => $text,
                    'metadata' => [
                        'response' => $response->toArray(),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Установить webhook URL для бота
     */
    public function setWebhook(string $webhookUrl): array
    {
        try {
            $response = $this->telegram->setWebhook(['url' => $webhookUrl]);

            Log::info('Telegram webhook set successfully', [
                'webhook_url' => $webhookUrl,
                'response' => $response
            ]);

            return [
                'success' => true,
                'message' => 'Webhook установлен успешно',
                'data' => $response
            ];
        } catch (\Exception $e) {
            Log::error('Telegram webhook setup error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка установки webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Получить информацию о webhook
     */
    public function getWebhookInfo(): array
    {
        try {
            $response = $this->telegram->getWebhookInfo();

            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            Log::error('Telegram webhook info error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка получения информации о webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Удалить webhook
     */
    public function deleteWebhook(): array
    {
        try {
            $response = $this->telegram->removeWebhook();

            Log::info('Telegram webhook deleted successfully');

            return [
                'success' => true,
                'message' => 'Webhook удален успешно',
                'data' => $response
            ];
        } catch (\Exception $e) {
            Log::error('Telegram webhook delete error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка удаления webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Отправить тестовое сообщение
     */
    public function sendTestMessage(int $chatId, string $message): bool
    {
        try {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
