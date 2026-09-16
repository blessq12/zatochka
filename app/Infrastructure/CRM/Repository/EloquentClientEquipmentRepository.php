<?php

namespace App\Infrastructure\CRM\Repository;

use App\Domain\CRM\Entity\ClientEquipment;
use App\Domain\CRM\Repository\ClientEquipmentRepository;
use App\Infrastructure\CRM\Mapper\ClientEquipmentMapper;
use App\Infrastructure\CRM\Model\ClientEquipmentModel;
use App\Infrastructure\CRM\Model\EquipmentComponentModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentClientEquipmentRepository implements ClientEquipmentRepository
{
    public function __construct(
        private ClientEquipmentMapper $mapper,
    ) {}

    public function save(ClientEquipment $equipment): void
    {
        DB::transaction(function () use ($equipment): void {
            $model = ClientEquipmentModel::query()->find($equipment->id()->value);
            $model = $this->mapper->toPersistence($equipment, $model);
            $model->save();

            EquipmentComponentModel::query()->where('equipment_id', $equipment->id()->value)->delete();

            foreach ($this->mapper->componentsToPersistence($equipment) as $row) {
                $row->save();
            }
        });
    }

    public function findById(EntityId $id): ?ClientEquipment
    {
        $model = ClientEquipmentModel::query()->with(['components'])->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): ClientEquipment
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Equipment %d not found.', $id->value));
    }

    public function listByClientId(EntityId $clientId): array
    {
        return ClientEquipmentModel::query()
            ->with(['components'])
            ->where('client_id', $clientId->value)
            ->get()
            ->map(fn ($model) => $this->mapper->toDomain($model))
            ->all();
    }
}
