<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientPasswordResetRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientPasswordResetController extends Controller
{
    /**
     * Отправить ссылку для сброса пароля
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|exists:clients,phone',
        ]);

        $status = Password::broker('clients')->sendResetLink(
            $request->only('phone')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Ссылка для сброса пароля отправлена на ваш телефон',
            ]);
        }

        throw ValidationException::withMessages([
            'phone' => [trans($status)],
        ]);
    }

    /**
     * Сбросить пароль
     */
    public function reset(ClientPasswordResetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $status = Password::broker('clients')->reset(
            $validated,
            function (Client $client, string $password) {
                $client->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Удаляем все токены клиента
                $client->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Пароль успешно сброшен',
            ]);
        }

        throw ValidationException::withMessages([
            'phone' => [trans($status)],
        ]);
    }
}
