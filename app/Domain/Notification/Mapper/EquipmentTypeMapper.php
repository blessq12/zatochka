<?php

namespace App\Domain\Notification\Mapper;

use App\Domain\Notification\Entity\EquipmentType;

interface EquipmentTypeMapper
{
    public function toDomain($eloquentModel): EquipmentType;
    public function toEloquent(EquipmentType $domainEntity): array;
}
