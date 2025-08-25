<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientService
{
    /**
     * Создать нового клиента
     */
    public function createClient(array $data): Client
    {
        return Client::create([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'telegram' => $data['telegram'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Найти клиента по телефону
     */
    public function findByPhone(string $phone): ?Client
    {
        return Client::where('phone', $phone)->first();
    }

    /**
     * Аутентифицировать клиента
     */
    public function authenticate(string $phone, string $password): ?Client
    {
        $client = $this->findByPhone($phone);

        if (!$client || !Hash::check($password, $client->password)) {
            return null;
        }

        return $client;
    }

    /**
     * Обновить профиль клиента
     */
    public function updateProfile(Client $client, array $data): bool
    {
        $updateData = [];

        if (isset($data['full_name'])) {
            $updateData['full_name'] = $data['full_name'];
        }

        if (isset($data['telegram'])) {
            $updateData['telegram'] = $data['telegram'];
        }

        if (isset($data['birth_date'])) {
            $updateData['birth_date'] = $data['birth_date'];
        }

        if (isset($data['delivery_address'])) {
            $updateData['delivery_address'] = $data['delivery_address'];
        }

        return $client->update($updateData);
    }

    /**
     * Изменить пароль клиента
     */
    public function changePassword(Client $client, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $client->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Неверный текущий пароль'],
            ]);
        }

        return $client->update([
            'password' => Hash::make($newPassword)
        ]);
    }

    /**
     * Отметить Telegram как верифицированный
     */
    public function markTelegramAsVerified(Client $client): bool
    {
        return $client->update(['telegram_verified_at' => now()]);
    }

    /**
     * Создать токен для клиента
     */
    public function createToken(Client $client, string $name = 'client-auth'): string
    {
        // Удаляем старые токены
        $client->tokens()->delete();

        return $client->createToken($name)->plainTextToken;
    }



    /**
     * Получить статистику клиента
     */
    public function getClientStats(Client $client): array
    {
        return [
            'total_orders' => $client->orders()->count(),
            'completed_orders' => $client->orders()->where('status', 'completed')->count(),
            'total_spent' => $client->orders()->sum('total_amount'),
            'last_order_date' => $client->orders()->latest()->first()?->created_at,
        ];
    }
}
