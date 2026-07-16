<?php

namespace App\Infrastructure\Pricing\Repository;

use App\Domain\Pricing\Entity\WorkPrice;
use App\Domain\Pricing\Repository\WorkPriceRepository;
use App\Infrastructure\Pricing\Mapper\WorkPriceMapper;
use App\Infrastructure\Pricing\Model\WorkPriceModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentWorkPriceRepository implements WorkPriceRepository
{
    public function __construct(
        private WorkPriceMapper $mapper,
    ) {}

    public function getById(EntityId $id): WorkPrice
    {
        $model = WorkPriceModel::query()->find($id->value);

        if ($model === null) {
            throw new DomainException(sprintf('Work price #%d not found.', $id->value));
        }

        return $this->mapper->toDomain($model);
    }

    public function findByPerformedWorkId(EntityId $performedWorkId): ?WorkPrice
    {
        $model = WorkPriceModel::query()
            ->where('performed_work_id', $performedWorkId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(WorkPrice $workPrice): void
    {
        $payload = $this->mapper->toPersistence($workPrice);

        WorkPriceModel::query()->updateOrCreate(
            ['id' => $payload->id],
            [
                'performed_work_id' => $payload->performed_work_id,
                'order_item_id' => $payload->order_item_id,
                'base_amount' => $payload->base_amount,
                'currency' => $payload->currency,
                'final_amount' => $payload->final_amount,
                'calculated' => $payload->calculated,
            ],
        );
    }

    public function deleteByPerformedWorkIds(array $performedWorkIds): void
    {
        if ($performedWorkIds === []) {
            return;
        }

        WorkPriceModel::query()
            ->whereIn('performed_work_id', $performedWorkIds)
            ->delete();
    }
}
