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

    public function findByMasterCommentId(EntityId $masterCommentId): ?WorkPrice
    {
        $model = WorkPriceModel::query()
            ->where('master_comment_id', $masterCommentId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(WorkPrice $workPrice): void
    {
        $this->mapper->toPersistence($workPrice)->save();
    }
}
