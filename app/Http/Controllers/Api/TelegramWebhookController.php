<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\TelegramWebhookServiceContract;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramWebhookServiceContract $webhookService;

    public function __construct(TelegramWebhookServiceContract $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Обработать входящий webhook от Telegram
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Получаем данные из webhook
            $data = $request->all();



            // Обрабатываем webhook
            $this->webhookService->handleWebhook($data);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Установить webhook URL для бота
     */
    public function setWebhook(Request $request): JsonResponse
    {
        try {
            $webhookUrl = $request->input('webhook_url');

            if (!$webhookUrl) {
                return response()->json(['error' => 'Webhook URL is required'], 400);
            }

            $result = $this->webhookService->setWebhook($webhookUrl);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Telegram webhook setup error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка установки webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить информацию о webhook
     */
    public function getWebhookInfo(): JsonResponse
    {
        try {
            $result = $this->webhookService->getWebhookInfo();

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 500);
            }
        } catch (\Exception $e) {
            Log::error('Telegram webhook info error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения информации о webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удалить webhook
     */
    public function deleteWebhook(): JsonResponse
    {
        try {
            $result = $this->webhookService->deleteWebhook();

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Telegram webhook delete error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отправить тестовое сообщение
     */
    public function sendTestMessage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'chat_id' => 'required|integer',
                'message' => 'required|string|max:4096'
            ]);

            $success = $this->webhookService->sendTestMessage(
                $request->input('chat_id'),
                $request->input('message')
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Тестовое сообщение отправлено'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка отправки тестового сообщения'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Telegram test message error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка отправки тестового сообщения: ' . $e->getMessage()
            ], 500);
        }
    }
}
