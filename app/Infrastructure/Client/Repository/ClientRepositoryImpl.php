<?php

namespace App\Infrastructure\Client\Repository;

use App\Domain\Client\Entity\Client as ClientEntity;
use App\Domain\Client\Mapper\ClientMapper;
use App\Domain\Client\Repository\ClientRepository;
use App\Models\Client;

class ClientRepositoryImpl implements ClientRepository
{
    public function __construct(
        private ClientMapper $clientMapper
    ) {}

    public function create(array $data): ClientEntity
    {
        $model = Client::create($data);
        return $this->clientMapper->toDomain($model);
    }

    public function get(string $id): ?ClientEntity
    {
        $model = Client::find($id);
        return $model ? $this->clientMapper->toDomain($model) : null;
    }

    public function update(ClientEntity $client, array $data): ClientEntity
    {
        $model = Client::find($client->getId());
        $model->update($data);
        return $this->clientMapper->toDomain($model->fresh());
    }

    public function delete(string $id): bool
    {
        return Client::where('id', $id)->update(['is_deleted' => true]) > 0;
    }

    public function existsByPhone(string $phone): bool
    {
        return Client::where('phone', $phone)->where('is_deleted', false)->exists();
    }

    public function findByPhone(string $phone): ?ClientEntity
    {
        $model = Client::where('phone', $phone)->where('is_deleted', false)->first();
        return $model ? $this->clientMapper->toDomain($model) : null;
    }
}
