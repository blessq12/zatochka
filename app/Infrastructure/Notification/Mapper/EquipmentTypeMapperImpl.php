<?php

namespace App\Infrastructure\Notification\Mapper;

use App\Domain\Notification\Entity\EquipmentType;
use App\Domain\Notification\Mapper\EquipmentTypeMapper;

class EquipmentTypeMapperImpl implements EquipmentTypeMapper
{
    public function toDomain($eloquentModel): EquipmentType
    {
        // TODO: Implement toDomain logic
        return new EquipmentType(id: $eloquentModel->id ?? null);
    }

    public function toEloquent(EquipmentType $domainEntity): array
    {
        // TODO: Implement toEloquent logic
        return [];
    }
}
