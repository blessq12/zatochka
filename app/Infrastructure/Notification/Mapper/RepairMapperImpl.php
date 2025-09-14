<?php

namespace App\Infrastructure\Notification\Mapper;

use App\Domain\Notification\Entity\Repair;
use App\Domain\Notification\Mapper\RepairMapper;

class RepairMapperImpl implements RepairMapper
{
    public function toDomain($eloquentModel): Repair
    {
        // TODO: Implement toDomain logic
        return new Repair(id: $eloquentModel->id ?? null);
    }

    public function toEloquent(Repair $domainEntity): array
    {
        // TODO: Implement toEloquent logic
        return [];
    }
}
