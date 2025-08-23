<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ClientTelegramVerificationController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Отправить код верификации на Telegram
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $client = $request->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован',
            ], 401);
        }

        // Проверяем, есть ли у клиента Telegram
        if (!$client->telegram) {
            throw ValidationException::withMessages([
                'telegram' => ['У вас не указан Telegram аккаунт'],
            ]);
        }

        // Проверяем, не верифицирован ли уже
        if ($client->isTelegramVerified()) {
            throw ValidationException::withMessages([
                'telegram' => ['Telegram уже верифицирован'],
            ]);
        }

        // Генерируем код верификации
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Сохраняем код в кеше на 10 минут
        $cacheKey = "telegram_verification_{$client->phone}";
        Cache::put($cacheKey, $verificationCode, 600); // 10 минут

        // Отправляем код через Telegram
        try {
            $result = $this->telegramService->sendVerificationCode($client->telegram, $verificationCode);
            
            if (!$result) {
                Cache::forget($cacheKey);
                throw ValidationException::withMessages([
                    'telegram' => ['Не удалось отправить код. Убедитесь, что вы начали диалог с ботом @zatochka_tsk_bot'],
                ]);
            }
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            Log::error('Telegram verification code sending failed', [
                'username' => $client->telegram,
                'error' => $e->getMessage()
            ]);
            throw ValidationException::withMessages([
                'telegram' => ['Не удалось отправить код. Убедитесь, что вы начали диалог с ботом @zatochka_tsk_bot'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Код верификации отправлен на ваш Telegram',
        ]);
    }

    /**
     * Проверить код верификации
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $client = $request->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован',
            ], 401);
        }

        $cacheKey = "telegram_verification_{$client->phone}";
        $storedCode = Cache::get($cacheKey);

        if (!$storedCode) {
            throw ValidationException::withMessages([
                'code' => ['Код верификации истек или не был отправлен'],
            ]);
        }

        if ($storedCode !== $request->code) {
            throw ValidationException::withMessages([
                'code' => ['Неверный код верификации'],
            ]);
        }

        // Верифицируем клиента
        $client->markTelegramAsVerified();

        // Удаляем код из кеша
        Cache::forget($cacheKey);

        // Отправляем уведомление об успешной верификации
        try {
            $this->telegramService->sendVerificationSuccess($client->telegram);
        } catch (\Exception $e) {
            Log::error('Telegram verification success notification failed', [
                'username' => $client->telegram,
                'error' => $e->getMessage()
            ]);
            // Игнорируем ошибку отправки уведомления
        }

        return response()->json([
            'success' => true,
            'message' => 'Telegram успешно верифицирован',
            'data' => [
                'client' => new \App\Http\Resources\ClientResource($client),
            ]
        ]);
    }

    /**
     * Проверить статус верификации
     */
    public function checkVerificationStatus(Request $request): JsonResponse
    {
        $client = $request->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_verified' => $client->isTelegramVerified(),
                'telegram' => $client->telegram,
                'telegram_verified_at' => $client->telegram_verified_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Обновить Telegram аккаунт
     */
    public function updateTelegram(Request $request): JsonResponse
    {
        $request->validate([
            'telegram' => 'required|string|max:50',
        ]);

        $client = $request->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован',
            ], 401);
        }

        // Если Telegram уже верифицирован и меняется на другой, сбрасываем верификацию
        if ($client->isTelegramVerified() && $client->telegram !== $request->telegram) {
            $client->update([
                'telegram' => $request->telegram,
                'telegram_verified_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Telegram аккаунт обновлен. Необходима повторная верификация.',
            ]);
        }

        $client->update(['telegram' => $request->telegram]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram аккаунт обновлен',
        ]);
    }
}
