<?php

namespace App\Infrastructure\CRM\Repository;

use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\Repository\ClientRepository;
use App\Infrastructure\CRM\Mapper\ClientMapper;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final readonly class EloquentClientRepository implements ClientRepository
{
    public function __construct(
        private ClientMapper $mapper,
    ) {}

    public function save(Client $client): void
    {
        $model = ClientModel::query()->find($client->id()->value);
        $model = $this->mapper->toPersistence($client, $model);
        $model->save();
    }

    public function findById(EntityId $id): ?Client
    {
        $model = ClientModel::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Client
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Client %d not found.', $id->value));
    }

    public function findByPhone(Phone $phone): ?Client
    {
        $model = ClientModel::query()->where('phone', $phone->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function findByEmail(Email $email): ?Client
    {
        $model = ClientModel::query()->where('email', $email->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
