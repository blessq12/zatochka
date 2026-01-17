<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\TelegramChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Обработка вебхука от Telegram
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $update = $request->all();

            Log::info('Telegram webhook received', ['update' => $update]);

            // Обрабатываем только сообщения
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $username = $message['chat']['username'] ?? null;
            $firstName = $message['chat']['first_name'] ?? null;
            $text = $message['text'] ?? null;

            if (!$chatId) {
                return response()->json(['ok' => true, 'error' => 'No chat_id']);
            }

            // Находим или создаем/обновляем чат
            $telegramChat = TelegramChat::byChatId($chatId)->first();

            if (!$telegramChat) {
                $telegramChat = TelegramChat::create([
                    'chat_id' => $chatId,
                    'username' => $username ?? '',
                    'metadata' => [
                        'first_name' => $firstName,
                        'last_name' => $message['chat']['last_name'] ?? null,
                    ],
                    'is_active' => true,
                ]);
            } else {
                // Обновляем username если он изменился или был пустым
                if ($username && ($telegramChat->username !== $username || empty($telegramChat->username))) {
                    $metadata = $telegramChat->metadata ?? [];
                    $metadata['first_name'] = $firstName;
                    if (isset($message['chat']['last_name'])) {
                        $metadata['last_name'] = $message['chat']['last_name'];
                    }
                    
                    $telegramChat->update([
                        'username' => $username,
                        'metadata' => $metadata,
                    ]);
                }
            }

            // Обновляем username в чате для передачи в handleMessage
            $telegramChat->refresh();

            // Обрабатываем все текстовые сообщения и команды одинаково
            if ($text) {
                $this->handleMessage($telegramChat, $text, $chatId);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Отправка кода верификации
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $client = auth('client')->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                'message' => 'Unauthorized',
                ], 401);
            }

        if (!$client->telegram) {
                return response()->json([
                    'success' => false,
                'message' => 'Telegram username not specified in profile',
                ], 400);
            }

        if ($client->telegram_verified_at) {
                return response()->json([
                    'success' => false,
                'message' => 'Telegram already verified',
                ], 400);
            }

        // Генерируем 6-значный код
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Сохраняем код в кеш на 5 минут (ключ: client_id + username)
        $cacheKey = "telegram_verification:{$client->id}:{$client->telegram}";
        Cache::put($cacheKey, [
            'code' => $code,
            'client_id' => $client->id,
            'username' => $client->telegram,
        ], now()->addMinutes(5));

        // Находим чат по username
        $telegramChat = TelegramChat::byUsername($client->telegram)->active()->first();

        if (!$telegramChat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found. Please send /start to the bot first',
            ], 404);
        }

        // Отправляем код в Telegram
        $botToken = config('services.telegram.bot_token');
        $message = "🔐 Код верификации: <b>{$code}</b>\n\nВведите этот код в личном кабинете или отправьте мне для подтверждения.";
        $this->sendMessage($botToken, $telegramChat->chat_id, $message);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent',
            'telegram_username' => $client->telegram,
            'expires_in_minutes' => 5,
        ]);
    }

    /**
     * Проверка кода верификации
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
                'code' => 'required|string|size:6',
            ]);

        $client = auth('client')->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                'message' => 'Unauthorized',
                ], 401);
            }

        if (!$client->telegram) {
                return response()->json([
                    'success' => false,
                'message' => 'Telegram username not specified',
                ], 400);
            }

        $code = $request->input('code');

        // Проверяем код в кеше
        $cacheKey = "telegram_verification:{$client->id}:{$client->telegram}";
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['code'] !== $code) {
                return response()->json([
                    'success' => false,
                'message' => 'Invalid or expired verification code',
                ], 400);
            }

        // Находим чат
        $telegramChat = TelegramChat::byUsername($client->telegram)->active()->first();

        if (!$telegramChat) {
                return response()->json([
                    'success' => false,
                'message' => 'Telegram chat not found',
            ], 404);
        }

        // Связываем чат с клиентом
        $telegramChat->update([
            'client_id' => $client->id,
        ]);

        // Обновляем клиента
            $client->update([
                'telegram_verified_at' => now(),
            ]);

        // Удаляем код из кеша
        Cache::forget($cacheKey);

        // Отправляем подтверждение в Telegram с клавиатурой
        $botToken = config('services.telegram.bot_token');
        $message = "✅ Telegram успешно подтвержден!\n\nТеперь вы будете получать уведомления о заказах автоматически.\n\nИспользуйте кнопки ниже для навигации:";
        $this->sendMessage($botToken, $telegramChat->chat_id, $message, true);

            return response()->json([
                'success' => true,
            'message' => 'Telegram verified successfully',
            'telegram_username' => $client->telegram,
            'verified_at' => $client->telegram_verified_at->toIso8601String(),
                'client' => $client->fresh(),
            ]);
    }

    /**
     * Проверка существования чата
     */
    public function checkChatExists(Request $request): JsonResponse
    {
        $client = auth('client')->user();

            if (!$client) {
                return response()->json([
                'chat_exists' => false,
                'message' => 'Unauthorized',
                ], 401);
            }

        if (!$client->telegram) {
            return response()->json([
                'chat_exists' => false,
                'message' => 'Telegram username not specified',
            ], 400);
        }

        $telegramChat = TelegramChat::byUsername($client->telegram)->active()->first();

        return response()->json([
            'chat_exists' => $telegramChat !== null,
        ]);
    }

    /**
     * Обработка всех сообщений (команды и текст)
     */
    private function handleMessage(TelegramChat $chat, string $text, int $chatId): void
    {
        $botToken = config('services.telegram.bot_token');
        $username = $chat->username;

        // Определяем статус пользователя: сначала по client_id из чата, потом по username
        $client = null;
        
        if ($chat->client_id) {
            // Если чат уже привязан к клиенту
            $client = Client::find($chat->client_id);
        } elseif ($username) {
            // Ищем клиента по username
            $client = Client::where('telegram', $username)->first();
        }

        $command = trim(strtolower($text));

        // Обрабатываем команду /start
        if ($command === '/start') {
            $this->handleStartCommand($botToken, $chatId, $client, $username);
            return;
        }

        // Команды только для подтвержденных пользователей
        if ($client && $client->telegram_verified_at) {
            // Обработка команд через текст (старые команды)
            if ($command === '/account' || $command === '/profile') {
                $this->handleAccountCommand($botToken, $chatId, $client);
                return;
            }

            if ($command === '/orders' || $command === '/active') {
                $this->handleActiveOrdersCommand($botToken, $chatId, $client);
                return;
            }

            if ($command === '/history' || $command === '/archive') {
                $this->handleHistoryOrdersCommand($botToken, $chatId, $client);
                return;
            }

            // Обработка нажатий кнопок клавиатуры
            $buttonText = trim($text);
            if ($buttonText === '👤 Аккаунт' || $buttonText === 'Аккаунт') {
                $this->handleAccountCommand($botToken, $chatId, $client);
                return;
            }

            if ($buttonText === '📋 Активные заказы' || $buttonText === 'Активные заказы') {
                $this->handleActiveOrdersCommand($botToken, $chatId, $client);
                return;
            }

            if ($buttonText === '📚 История заказов' || $buttonText === 'История заказов') {
                $this->handleHistoryOrdersCommand($botToken, $chatId, $client);
                return;
            }
        }

        // Проверяем, является ли сообщение 6-значным кодом
        if (preg_match('/^\d{6}$/', trim($text))) {
            $this->handleVerificationCodeFromBot($chat, trim($text), $chatId, $client, $username);
            return;
        }

        // Обрабатываем обычные сообщения в зависимости от статуса
        $this->handleRegularMessage($botToken, $chatId, $client, $username);
    }

    /**
     * Обработка команды /start
     */
    private function handleStartCommand(string $botToken, int $chatId, ?Client $client, ?string $username): void
    {
        if (!$client) {
            // Пользователя нет в БД
            if ($username) {
                $message = "❌ Ваш Telegram username (@{$username}) не найден в нашей базе данных.\n\nДля использования бота:\n1. Зарегистрируйтесь на сайте\n2. Укажите ваш Telegram username (@{$username}) в личном кабинете\n3. Затем нажмите /start снова";
            } else {
                $message = "❌ Ваш аккаунт не найден в нашей базе данных.\n\nДля использования бота:\n1. Зарегистрируйтесь на сайте\n2. Укажите ваш Telegram username в личном кабинете\n3. Затем нажмите /start снова";
            }
            $this->sendMessage($botToken, $chatId, $message);
            return;
        }

        if ($client->telegram_verified_at) {
            // Telegram подтвержден - показываем клавиатуру
            $message = "✅ Ваш Telegram уже подтвержден!\n\n";
            $message .= "Теперь вы будете получать уведомления о статусе ваших заказов автоматически.\n\n";
            $message .= "Используйте кнопки ниже для навигации:";
            $this->sendMessage($botToken, $chatId, $message, true);
            return;
        }

        // Username есть, но не подтвержден
        if ($username) {
            $message = "👋 Добро пожаловать!\n\nВаш Telegram username (@{$username}) найден в базе данных, но еще не подтвержден.\n\nДля подтверждения:\n1. Перейдите в личный кабинет на сайте\n2. Нажмите кнопку 'Отправить код подтверждения'\n3. Введите полученный 6-значный код здесь";
        } else {
            $message = "👋 Добро пожаловать!\n\nВаш аккаунт найден, но Telegram еще не подтвержден.\n\nДля подтверждения:\n1. Перейдите в личный кабинет на сайте\n2. Укажите ваш Telegram username и нажмите 'Отправить код подтверждения'\n3. Введите полученный 6-значный код здесь";
        }
        $this->sendMessage($botToken, $chatId, $message);
    }

    /**
     * Обработка обычных сообщений
     */
    private function handleRegularMessage(string $botToken, int $chatId, ?Client $client, ?string $username): void
    {
        if (!$client) {
            // Пользователя нет в БД
            if ($username) {
                $message = "❌ Ваш Telegram username (@{$username}) не найден в базе данных.\n\nЗарегистрируйтесь на сайте и укажите ваш Telegram username в личном кабинете.";
            } else {
                $message = "❌ Ваш аккаунт не найден в базе данных.\n\nЗарегистрируйтесь на сайте и укажите ваш Telegram username в личном кабинете.";
            }
            $this->sendMessage($botToken, $chatId, $message);
            return;
        }

        if ($client->telegram_verified_at) {
            // Telegram подтвержден - показываем клавиатуру и подсказку
            $message = "Используйте кнопки ниже для навигации или отправьте /start для главного меню.";
            $this->sendMessage($botToken, $chatId, $message, true);
            return;
        }

        // Username есть, но не подтвержден
        $message = "⏳ Ваш Telegram еще не подтвержден.\n\nДля подтверждения:\n1. Перейдите в личный кабинет\n2. Нажмите 'Отправить код подтверждения'\n3. Введите полученный код здесь";
        $this->sendMessage($botToken, $chatId, $message);
    }

    /**
     * Обработка кода верификации из бота
     */
    private function handleVerificationCodeFromBot(TelegramChat $chat, string $code, int $chatId, ?Client $client, ?string $username): void
    {
        $botToken = config('services.telegram.bot_token');

        if (!$client) {
            // Пользователя нет в БД
            if ($username) {
                $message = "❌ Ваш Telegram username (@{$username}) не найден в базе данных.\n\nЗарегистрируйтесь на сайте и укажите ваш Telegram username в личном кабинете.";
            } else {
                $message = "❌ Ваш аккаунт не найден в базе данных.\n\nЗарегистрируйтесь на сайте и укажите ваш Telegram username в личном кабинете.";
            }
            $this->sendMessage($botToken, $chatId, $message);
            return;
        }

        if ($client->telegram_verified_at) {
            // Уже подтвержден
            $message = "✅ Ваш Telegram уже подтвержден! Вы будете получать уведомления о заказах автоматически.\n\nИспользуйте кнопки ниже для навигации:";
            $this->sendMessage($botToken, $chatId, $message, true);
            return;
        }

        if (!$client->telegram) {
            $message = "❌ Telegram username не указан в вашем профиле.\n\nУкажите ваш Telegram username в личном кабинете и запросите код подтверждения.";
            $this->sendMessage($botToken, $chatId, $message);
            return;
        }

        // Проверяем код в кеше
        $cacheKey = "telegram_verification:{$client->id}:{$client->telegram}";
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['code'] !== $code) {
            $this->sendMessage($botToken, $chatId, "❌ Неверный или истекший код верификации.\n\nЗапросите новый код в личном кабинете (кнопка 'Отправить код подтверждения').");
            return;
        }

        // Связываем чат с клиентом
        $chat->update([
            'client_id' => $client->id,
        ]);

        // Обновляем клиента
        $client->update([
            'telegram_verified_at' => now(),
        ]);

        // Удаляем код из кеша
        Cache::forget($cacheKey);

        $message = "✅ Telegram успешно подтвержден!\n\nТеперь вы будете получать уведомления о статусе ваших заказов автоматически.\n\nИспользуйте кнопки ниже для навигации:";
        $this->sendMessage($botToken, $chatId, $message, true);
    }

    /**
     * Обработка команды /account - информация об аккаунте
     */
    private function handleAccountCommand(string $botToken, int $chatId, Client $client): void
    {
        $message = "👤 <b>Информация об аккаунте</b>\n\n";
        $message .= "Имя: {$client->full_name}\n";
        
        if ($client->phone) {
            $message .= "Телефон: {$client->phone}\n";
        }
        
        if ($client->email) {
            $message .= "Email: {$client->email}\n";
        }
        
        if ($client->telegram) {
            $message .= "Telegram: @{$client->telegram}\n";
        }
        
        if ($client->delivery_address) {
            $message .= "Адрес доставки: {$client->delivery_address}\n";
        }
        
        if ($client->birth_date) {
            $birthDate = is_string($client->birth_date) 
                ? \Carbon\Carbon::parse($client->birth_date)->format('d.m.Y')
                : $client->birth_date->format('d.m.Y');
            $message .= "Дата рождения: {$birthDate}\n";
        }

        if ($client->telegram_verified_at) {
            $verifiedDate = $client->telegram_verified_at instanceof \Carbon\Carbon
                ? $client->telegram_verified_at->format('d.m.Y H:i')
                : \Carbon\Carbon::parse($client->telegram_verified_at)->format('d.m.Y H:i');
            $message .= "\n✅ Telegram подтвержден: {$verifiedDate}";
        }

        // Добавляем информацию о бонусах
        $bonusPoints = $client->bonus_points ?? 0;
        $message .= "\n\n🎁 <b>Бонусные баллы:</b> {$bonusPoints}";

        $this->sendMessage($botToken, $chatId, $message, true);
    }

    /**
     * Обработка команды /orders - активные заказы
     */
    private function handleActiveOrdersCommand(string $botToken, int $chatId, Client $client): void
    {
        // Получаем активные заказы (статус не issued и не cancelled)
        $activeOrders = $client->orders()
            ->whereNotIn('status', [\App\Models\Order::STATUS_ISSUED, \App\Models\Order::STATUS_CANCELLED])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($activeOrders->isEmpty()) {
            $message = "📋 <b>Активные заказы</b>\n\nУ вас нет активных заказов.";
            $this->sendMessage($botToken, $chatId, $message, true);
            return;
        }

        $message = "📋 <b>Активные заказы</b> (" . $activeOrders->count() . ")\n\n";

        foreach ($activeOrders as $order) {
            $statusLabels = \App\Models\Order::getAvailableStatuses();
            $typeLabels = \App\Models\Order::getAvailableTypes();
            $statusLabel = $statusLabels[$order->status] ?? $order->status;
            $typeLabel = $typeLabels[$order->service_type] ?? $order->service_type;

            $message .= "🔹 <b>{$order->order_number}</b>\n";
            $message .= "Тип: {$typeLabel}\n";
            $message .= "Статус: {$statusLabel}\n";
            
            if ($order->estimated_price) {
                $price = $this->formatPrice($order->estimated_price);
                if ($price) {
                    $message .= "Цена: {$price}\n";
                }
            }
            
            $message .= "Создан: " . $order->created_at->format('d.m.Y H:i') . "\n\n";
        }

        $this->sendMessage($botToken, $chatId, $message, true);
    }

    /**
     * Обработка команды /history - архивные заказы
     */
    private function handleHistoryOrdersCommand(string $botToken, int $chatId, Client $client): void
    {
        // Получаем архивные заказы (статус issued или cancelled)
        $archivedOrders = $client->orders()
            ->whereIn('status', [\App\Models\Order::STATUS_ISSUED, \App\Models\Order::STATUS_CANCELLED])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($archivedOrders->isEmpty()) {
            $message = "📚 <b>История заказов</b>\n\nУ вас нет завершенных заказов.";
            $this->sendMessage($botToken, $chatId, $message, true);
            return;
        }

        $message = "📚 <b>История заказов</b> (" . $archivedOrders->count() . ")\n\n";

        foreach ($archivedOrders as $order) {
            $statusLabels = \App\Models\Order::getAvailableStatuses();
            $typeLabels = \App\Models\Order::getAvailableTypes();
            $statusLabel = $statusLabels[$order->status] ?? $order->status;
            $typeLabel = $typeLabels[$order->service_type] ?? $order->service_type;

            $statusIcon = $order->status === \App\Models\Order::STATUS_ISSUED ? '✅' : '❌';
            
            $message .= "{$statusIcon} <b>{$order->order_number}</b>\n";
            $message .= "Тип: {$typeLabel}\n";
            $message .= "Статус: {$statusLabel}\n";
            
            if ($order->actual_price) {
                $price = $this->formatPrice($order->actual_price);
                if ($price) {
                    $message .= "Итоговая цена: {$price}\n";
                }
            } elseif ($order->estimated_price) {
                $price = $this->formatPrice($order->estimated_price);
                if ($price) {
                    $message .= "Цена: {$price}\n";
                }
            }
            
            $message .= "Завершен: " . $order->updated_at->format('d.m.Y H:i') . "\n\n";
        }

        $this->sendMessage($botToken, $chatId, $message, true);
    }

    /**
     * Получение клавиатуры для подтвержденных пользователей
     */
    private function getMainKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '👤 Аккаунт'],
                ],
                [
                    ['text' => '📋 Активные заказы'],
                    ['text' => '📚 История заказов'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Форматирование цены для отображения
     */
    private function formatPrice($price): ?string
    {
        if (!$price || $price === '0' || $price === 0) {
            return null;
        }

        $priceFloat = (float) $price;

        // Если цена целое число - без копеек
        if ((int) $priceFloat == $priceFloat) {
            return number_format($priceFloat, 0, '', ' ') . '₽';
        }

        // С копейками
        return number_format($priceFloat, 2, ',', ' ') . '₽';
    }

    /**
     * Отправка сообщения через Telegram Bot API
     */
    private function sendMessage(string $botToken, int $chatId, string $text, bool $showKeyboard = false): void
    {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        // Добавляем клавиатуру если нужно
        if ($showKeyboard) {
            $data['reply_markup'] = $this->getMainKeyboard();
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error('Telegram send message failed', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram send message exception: ' . $e->getMessage());
        }
    }
}
