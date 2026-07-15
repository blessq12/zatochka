<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryRequest;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Infrastructure\Delivery\Mapper\DeliveryRequestMapper;
use App\Infrastructure\Delivery\Model\DeliveryRequestModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentDeliveryRequestRepository implements DeliveryRequestRepository
{
    public function __construct(
        private DeliveryRequestMapper $mapper,
    ) {}

    public function save(DeliveryRequest $request): void
    {
        $model = DeliveryRequestModel::query()->find($request->id()->value);
        $model = $this->mapper->toPersistence($request, $model);
        $model->save();
    }

    public function findById(EntityId $id): ?DeliveryRequest
    {
        $model = DeliveryRequestModel::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): DeliveryRequest
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Delivery request %d not found.', $id->value));
    }
}
