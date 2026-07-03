<?php

namespace App\Infrastructure\ClientPortal\Persistence\Repository;

use App\Domain\ClientPortal\Entity\Client;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Infrastructure\ClientPortal\Persistence\Mapper\ClientMapper;

final class EloquentClientRepository implements ClientRepositoryInterface
{
    public function __construct(
        private ClientMapper $mapper,
    ) {}

    public function findById(int $id): ?Client
    {
        $model = ClientModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByPhone(string $phone): ?Client
    {
        $model = ClientModel::query()->where('phone', $phone)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Client $client, ?string $hashedPassword = null): Client
    {
        $model = $client->id() !== null
            ? ClientModel::query()->findOrFail($client->id())
            : new ClientModel;

        $this->mapper->fillModel($client, $model);

        if ($hashedPassword !== null) {
            $model->password = $hashedPassword;
        }

        $model->save();

        return $this->mapper->toDomain($model);
    }
}
