<?php

namespace App\Infrastructure\CRM\Repository;

use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\Repository\ClientRepository;
use App\Infrastructure\CRM\Mapper\ClientMapper;
use App\Infrastructure\CRM\Model\ClientHistoryModel;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;
use Illuminate\Support\Facades\DB;

final readonly class EloquentClientRepository implements ClientRepository
{
    public function __construct(
        private ClientMapper $mapper,
    ) {}

    public function save(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            $model = ClientModel::query()->find($client->id()->value);
            $model = $this->mapper->toPersistence($client, $model);
            $model->save();

            ClientHistoryModel::query()->where('client_id', $client->id()->value)->delete();

            foreach ($this->mapper->historyToPersistence($client) as $row) {
                $row->save();
            }
        });
    }

    public function findById(EntityId $id): ?Client
    {
        $model = ClientModel::query()->with('history')->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Client
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Client %d not found.', $id->value));
    }

    public function findByPhone(Phone $phone): ?Client
    {
        $model = ClientModel::query()->with('history')->where('phone', $phone->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function findByEmail(Email $email): ?Client
    {
        $model = ClientModel::query()->with('history')->where('email', $email->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
