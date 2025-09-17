<?php

namespace App\Application\UseCases\Auth\Api;

use App\Application\UseCases\Auth\Api\BaseClientAuthUseCase;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\AggregateRoot\ClientAggregateRoot;

class ApiLoginUseCase extends BaseClientAuthUseCase
{
    protected function validateSpecificData(): self
    {
        if (empty($this->data['phone'])) {
            throw new \InvalidArgumentException('Номер телефона обязателен');
        }

        if (empty($this->data['password'])) {
            throw new \InvalidArgumentException('Пароль обязателен');
        }

        return $this;
    }

    public function execute(): array
    {
        $phone = $this->data['phone'];
        $password = $this->data['password'];
        $client = $this->clientRepository->findByPhone($phone);

        if (!$client) {
            throw new \InvalidArgumentException('Клиент не найден');
        }

        if (!$client->verifyPassword($password)) {
            throw new \InvalidArgumentException('Неверный пароль');
        }

        $eloquentClient = \App\Models\Client::find($client->getId());
        $token = $eloquentClient->createToken('api-token')->plainTextToken;

        $aggregateRoot = ClientAggregateRoot::create();
        $aggregateRoot->loginClient($phone);
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
