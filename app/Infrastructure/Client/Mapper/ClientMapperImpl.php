<?php

namespace App\Infrastructure\Client\Mapper;

use App\Domain\Client\Entity\Client as ClientEntity;
use App\Domain\Client\Mapper\ClientMapper;
use App\Models\Client;

class ClientMapperImpl implements ClientMapper
{
    public function toDomain(Client $model): ClientEntity
    {
        return new ClientEntity(
            id: $model->id,
            fullName: $model->full_name,
            phone: $model->phone,
            telegram: $model->telegram,
            birthDate: $model->birth_date?->format('Y-m-d'),
            deliveryAddress: $model->delivery_address,
            isDeleted: (bool) $model->is_deleted,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    public function toEloquent(ClientEntity $entity): Client
    {
        $model = new Client();
        $model->id = $entity->getId();
        $model->full_name = $entity->getFullName();
        $model->phone = $entity->getPhone();
        $model->telegram = $entity->getTelegram();
        $model->birth_date = $entity->getBirthDate();
        $model->delivery_address = $entity->getDeliveryAddress();
        $model->is_deleted = $entity->isDeleted();
        $model->created_at = $entity->getCreatedAt();
        $model->updated_at = $entity->getUpdatedAt();

        return $model;
    }

    public function fromArray(array $data): ClientEntity
    {
        return new ClientEntity(
            id: $data['id'] ?? null,
            fullName: $data['full_name'],
            phone: $data['phone'],
            telegram: $data['telegram'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            isDeleted: (bool) ($data['is_deleted'] ?? false),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }
}
