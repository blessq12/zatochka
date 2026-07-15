<?php

namespace App\Domain\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryRequest;
use App\Shared\ValueObject\EntityId;

interface DeliveryRequestRepository
{
    public function save(DeliveryRequest $request): void;

    public function findById(EntityId $id): ?DeliveryRequest;

    public function getById(EntityId $id): DeliveryRequest;
}
