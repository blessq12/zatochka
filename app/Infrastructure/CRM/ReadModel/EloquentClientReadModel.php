<?php

namespace App\Infrastructure\CRM\ReadModel;

use App\Application\CRM\DTO\ClientDTO;
use App\Application\CRM\ReadPort\ClientReadPort;
use App\Infrastructure\CRM\Mapper\ClientMapper;
use App\Infrastructure\CRM\Model\ClientModel;

final readonly class EloquentClientReadModel implements ClientReadPort
{
    public function __construct(
        private ClientMapper $mapper,
    ) {}

    public function findById(int $clientId): ?ClientDTO
    {
        $model = ClientModel::query()->find($clientId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByPhone(string $phone): ?ClientDTO
    {
        $model = ClientModel::query()->where('phone', $phone)->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }
}
