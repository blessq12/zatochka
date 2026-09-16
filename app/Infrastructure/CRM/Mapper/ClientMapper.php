<?php

namespace App\Infrastructure\CRM\Mapper;

use App\Application\CRM\DTO\ClientDTO;
use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\ValueObject\AdditionalData;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final class ClientMapper
{
    public function toDomain(ClientModel $model): Client
    {
        $birthDate = $model->birth_date !== null
            ? ($model->birth_date instanceof \DateTimeInterface
                ? $model->birth_date->format('Y-m-d')
                : (string) $model->birth_date)
            : null;

        $additionalData = AdditionalData::empty()
            ->withBirthDate($birthDate)
            ->withDeliveryAddress(
                $model->delivery_address !== null ? (string) $model->delivery_address : null,
            );

        return Client::reconstitute(
            new EntityId((int) $model->id),
            new Phone((string) $model->phone),
            $model->name !== null ? (string) $model->name : null,
            $model->email !== null ? new Email((string) $model->email) : null,
            $additionalData,
        );
    }

    public function toPersistence(Client $client, ?ClientModel $model = null): ClientModel
    {
        $model ??= new ClientModel();
        $model->id = $client->id()->value;
        $model->phone = $client->phone()->value;
        $model->name = $client->name();
        $model->email = $client->email()?->value;
        $model->birth_date = $client->birthDate();
        $model->delivery_address = $client->deliveryAddress();

        return $model;
    }

    public function toDTO(ClientModel $model): ClientDTO
    {
        return new ClientDTO(
            (int) $model->id,
            (string) $model->phone,
            $model->name !== null ? (string) $model->name : null,
            $model->email !== null ? (string) $model->email : null,
            $model->birth_date !== null
                ? ($model->birth_date instanceof \DateTimeInterface
                    ? $model->birth_date->format('Y-m-d')
                    : (string) $model->birth_date)
                : null,
            $model->delivery_address !== null ? (string) $model->delivery_address : null,
        );
    }
}
