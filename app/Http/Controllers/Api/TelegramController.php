<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Communication\SendVerificationCodeUseCase;
use App\Application\UseCases\Communication\HandleTelegramCommandUseCase;
use App\Application\UseCases\Communication\HandleTelegramMessageUseCase;
use App\Application\UseCases\Communication\VerifyTelegramCodeUseCase;


use App\Http\Controllers\Controller;
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
            \Illuminate\Support\Facades\Log::info('Telegram webhook received', [
                'data' => json_encode($data),
            ]);

            if (!isset($data['message'])) {
                return response()->json(['status' => 'ok']);
            }

            $message = $data['message'];
            if ($this->isCommand($message)) {
                $result = (new HandleTelegramCommandUseCase())->loadData($data)->validate()->execute();
            } else {
                $result = (new HandleTelegramMessageUseCase())->loadData($data)->validate()->execute();
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Определяет является ли сообщение командой
     */
    private function isCommand(array $message): bool
    {
        // Проверяем entities на наличие bot_command
        if (isset($message['entities'])) {
            foreach ($message['entities'] as $entity) {
                if ($entity['type'] === 'bot_command') {
                    return true;
                }
            }
        }

        // Fallback: проверяем начинается ли текст с /
        return isset($message['text']) && str_starts_with($message['text'], '/');
    }

    public function telegramSendVerificationCode(Request $request)
    {
        try {
            $result = (new SendVerificationCodeUseCase())->loadData($request->all())->validate()->execute();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function telegramVerifyCode(Request $request)
    {
        $result = (new VerifyTelegramCodeUseCase())->loadData($request->all())->validate()->execute();
        return response()->json($result);
    }
}
