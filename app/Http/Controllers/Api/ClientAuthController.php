<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientLoginRequest;
use App\Http\Requests\ClientRegisterRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientAuthController extends Controller
{
    /**
     * Регистрация нового клиента
     */
    public function register(ClientRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Создаем клиента
        $client = Client::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'telegram' => $validated['telegram'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // Создаем токен
        $token = $client->createToken('client-auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Клиент успешно зарегистрирован',
            'data' => [
                'client' => new ClientResource($client),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Вход клиента
     */
    public function login(ClientLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Ищем клиента по телефону
        $client = Client::where('phone', $validated['phone'])->first();

        // Проверяем пароль
        if (!$client || !Hash::check($validated['password'], $client->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Неверный номер телефона или пароль'],
            ]);
        }

        // Удаляем старые токены
        $client->tokens()->delete();

        // Создаем новый токен
        $token = $client->createToken('client-auth')->plainTextToken;

        // Запоминаем пользователя если нужно
        if ($validated['remember'] ?? false) {
            $client->update(['remember_token' => Str::random(60)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Успешный вход',
            'data' => [
                'client' => new ClientResource($client),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Выход клиента
     */
    public function logout(Request $request): JsonResponse
    {
        // Удаляем текущий токен
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Успешный выход',
        ]);
    }

    /**
     * Получить профиль клиента
     */
    public function profile(Request $request): JsonResponse
    {
        $client = $request->user();

        return response()->json([
            'success' => true,
            'data' => new ClientResource($client->load('orders')),
        ]);
    }

    /**
     * Обновление профиля клиента
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $client = $request->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'telegram' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'delivery_address' => 'nullable|string|max:500',
        ]);

        // Проверяем, не занят ли телефон другим пользователем
        if ($validated['phone'] !== $client->phone) {
            $existingClient = Client::where('phone', $validated['phone'])
                ->where('id', '!=', $client->id)
                ->first();

            if ($existingClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Этот номер телефона уже используется'
                ], 422);
            }
        }

        // Обновляем данные клиента
        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Профиль успешно обновлен',
            'data' => [
                'client' => new ClientResource($client)
            ]
        ]);
    }

    /**
     * Изменение пароля
     */
    public function changePassword(Request $request): JsonResponse
    {
        $client = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        // Проверяем текущий пароль
        if (!Hash::check($validated['current_password'], $client->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Неверный текущий пароль'],
            ]);
        }

        // Обновляем пароль
        $client->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Удаляем все токены (принудительный выход)
        $client->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Пароль изменен. Необходимо войти заново.',
        ]);
    }

    /**
     * Проверить токен
     */
    public function checkToken(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Токен действителен',
            'data' => [
                'client' => new ClientResource($request->user()),
            ],
        ]);
    }
}
