<?php

namespace App\Infrastructure\ClientPortal\Persistence\Mapper;

use App\Domain\ClientPortal\Entity\Client;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;

final class ClientMapper
{
    public function toDomain(ClientModel $model): Client
    {
        return new Client(
            id: $model->id,
            phone: $model->phone,
            fullName: $model->full_name,
            email: $model->email,
            birthDate: $model->birth_date?->format('Y-m-d'),
            deliveryAddress: $model->delivery_address,
            requiresPasswordSet: $model->requires_password_set,
        );
    }

    public function fillModel(Client $client, ClientModel $model): void
    {
        $model->fill([
            'phone' => $client->phone(),
            'full_name' => $client->fullName(),
            'email' => $client->email(),
            'birth_date' => $client->birthDate(),
            'delivery_address' => $client->deliveryAddress(),
            'requires_password_set' => $client->requiresPasswordSet(),
        ]);
    }
}
