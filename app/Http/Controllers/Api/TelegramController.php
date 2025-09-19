<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Application\UseCases\Communication\ConnectTelegramUseCase;
use App\Application\UseCases\Communication\VerifyTelegramConnectionUseCase;
use App\Domain\Communication\Service\TelegramWebhookServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
            $data = $request->all();

            // Обрабатываем webhook через сервис
            $webhookService = app(TelegramWebhookServiceInterface::class);
            $result = $webhookService->handleWebhook($data);

            // Если это команда /start, запускаем Use Case
            if ($result['action'] === 'start_command') {
                $useCase = new ConnectTelegramUseCase();
                $useCase->loadData([
                    'username' => $result['username'],
                    'chat_id' => $result['chat_id']
                ])->validate()->execute();
            }

            return response()->json(['ok' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyCode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6',
                'chat_id' => 'required|string'
            ]);

            $useCase = new VerifyTelegramConnectionUseCase();
            $result = $useCase->loadData([
                'code' => $request->input('code'),
                'chat_id' => $request->input('chat_id')
            ])->validate()->execute();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
