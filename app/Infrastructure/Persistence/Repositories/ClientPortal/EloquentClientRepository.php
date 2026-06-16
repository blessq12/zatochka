<?php

namespace App\Infrastructure\Persistence\Repositories\ClientPortal;

use App\Domain\ClientPortal\Entities\Client;
use App\Domain\ClientPortal\Repositories\ClientRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ClientPortal\ClientModel;
use App\Infrastructure\Persistence\Mappers\ClientPortal\ClientMapper;

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

    public function save(Client $client): Client
    {
        $model = $client->id() !== null
            ? ClientModel::query()->findOrFail($client->id())
            : new ClientModel;

        $this->mapper->fillModel($client, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
