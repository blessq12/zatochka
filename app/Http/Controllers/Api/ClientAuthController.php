<?php

namespace App\Http\Controllers\Api;

use App\Application\ClientPortal\Command\RegisterClientCommand;
use App\Application\ClientPortal\Command\SetClientPasswordCommand;
use App\Application\ClientPortal\CommandHandler\RegisterClientHandler;
use App\Application\ClientPortal\CommandHandler\SetClientPasswordHandler;
use App\Application\ClientPortal\Presenter\ClientProfilePresenter;
use App\Infrastructure\ClientPortal\Auth\ClientAuthModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class ClientAuthController
{
    public function register(Request $request, RegisterClientHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'full_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $client = $handler->handle(new RegisterClientCommand(
            phone: $validated['phone'],
            fullName: $validated['full_name'],
            password: $validated['password'],
            email: $validated['email'] ?? null,
            birthDate: $validated['birth_date'] ?? null,
            deliveryAddress: $validated['delivery_address'] ?? null,
        ));

        $authModel = ClientAuthModel::query()->findOrFail($client->id());
        $token = $authModel->createToken('client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => ClientProfilePresenter::present($client),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $client = ClientAuthModel::query()->where('phone', $validated['phone'])->first();

        if ($client === null || ! Hash::check($validated['password'], $client->password)) {
            return response()->json(['message' => 'Неверные учётные данные.'], 401);
        }

        $token = $client->createToken('client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => [
                'id' => $client->id,
                'full_name' => $client->full_name,
                'phone' => $client->phone,
                'email' => $client->email,
                'requires_password_set' => $client->requires_password_set,
            ],
        ]);
    }

    public function setPassword(Request $request, SetClientPasswordHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $client = $handler->handle(new SetClientPasswordCommand(
            clientId: $this->clientId($request),
            password: $validated['password'],
        ));

        return response()->json([
            'data' => ClientProfilePresenter::present($client),
        ]);
    }

    private function clientId(Request $request): int
    {
        $user = $request->user();

        if (! $user instanceof ClientAuthModel) {
            abort(401, 'Требуется авторизация клиента.');
        }

        return $user->id;
    }
}
