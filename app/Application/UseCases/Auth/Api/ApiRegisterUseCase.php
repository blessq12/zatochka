<?php

namespace App\Application\UseCases\Auth\Api;

use App\Application\UseCases\Auth\Api\BaseClientAuthUseCase;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\AggregateRoot\ClientAggregateRoot;
use Illuminate\Support\Facades\Hash;

class ApiRegisterUseCase extends BaseClientAuthUseCase
{
    protected function validateSpecificData(): self
    {
        if (empty($this->data['full_name'])) {
            throw new \InvalidArgumentException('Полное имя обязательно');
        }

        if (empty($this->data['phone'])) {
            throw new \InvalidArgumentException('Номер телефона обязателен');
        }

        if (empty($this->data['password'])) {
            throw new \InvalidArgumentException('Пароль обязателен');
        }

        if (strlen($this->data['password']) < 6) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 6 символов');
        }

        if (empty($this->data['password_confirmation'])) {
            throw new \InvalidArgumentException('Подтверждение пароля обязательно');
        }

        if ($this->data['password'] !== $this->data['password_confirmation']) {
            throw new \InvalidArgumentException('Пароли не совпадают');
        }

        // Проверяем уникальность телефона
        if ($this->clientRepository->existsByPhone($this->data['phone'])) {
            throw new \InvalidArgumentException('Клиент с таким номером телефона уже существует');
        }

        return $this;
    }

    public function execute(): array
    {
        // Подготавливаем данные для создания клиента
        $clientData = [
            'full_name' => $this->data['full_name'],
            'phone' => $this->data['phone'],
            'telegram' => $this->data['telegram'] ?? null,
            'birth_date' => $this->data['birth_date'] ?? null,
            'delivery_address' => $this->data['delivery_address'] ?? null,
            'password' => Hash::make($this->data['password']),
            'is_deleted' => false,
        ];

        // Создаем клиента через Repository
        $client = $this->clientRepository->create($clientData);

        // Создаем токен для API (используем Eloquent модель для Sanctum)
        $eloquentClient = \App\Models\Client::find($client->getId());
        $token = $eloquentClient->createToken('api-token')->plainTextToken;

        // Записываем событие регистрации через Event Sourcing
        $aggregateRoot = ClientAggregateRoot::create();
        $aggregateRoot->registerClient($clientData);
        $aggregateRoot->persist();

        return [
            'token' => $token,
            'client' => [
                'id' => $client->getId(),
                'full_name' => $client->getFullName(),
                'phone' => $client->getPhone(),
                'telegram' => $client->getTelegram(),
                'birth_date' => $client->getBirthDate(),
                'delivery_address' => $client->getDeliveryAddress(),
            ],
        ];
    }
}
