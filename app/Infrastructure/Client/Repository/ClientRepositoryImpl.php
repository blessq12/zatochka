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

    public function findByPhoneAndPassword(string $phone, string $password): ?ClientEntity
    {
        $model = Client::where('phone', $phone)->where('is_deleted', false)->first();

        if (!$model || !password_verify($password, $model->password)) {
            return null;
        }

        return $this->clientMapper->toDomain($model);
    }

    public function existsByEmail(string $email): bool
    {
        return Client::where('email', $email)->where('is_deleted', false)->exists();
    }

    public function findByEmail(string $email): ?ClientEntity
    {
        $model = Client::where('email', $email)->where('is_deleted', false)->first();
        return $model ? $this->clientMapper->toDomain($model) : null;
    }

    public function updateTelegramVerification(string $id, \DateTime $verifiedAt): ClientEntity
    {
        $model = Client::findOrFail($id);
        $model->telegram_verified_at = $verifiedAt;
        $model->save();
        
        return $this->clientMapper->toDomain($model->fresh());
    }
}
