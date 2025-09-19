<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Communication\SendVerificationCodeUseCase;
use App\Application\UseCases\Communication\HandleTelegramCommandUseCase;
use App\Application\UseCases\Communication\HandleTelegramMessageUseCase;

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

            if (!isset($data['message'])) {
                return response()->json(['status' => 'ok']);
            }

            $message = $data['message'];

            if (isset($message['text']) && str_starts_with($message['text'], '/')) {
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
}
