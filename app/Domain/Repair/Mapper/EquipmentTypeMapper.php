<?php

namespace App\Domain\Repair\Mapper;

use App\Domain\Repair\Entity\EquipmentType;

interface EquipmentTypeMapper
{
    public function toDomain($eloquentModel): EquipmentType;
    public function toEloquent(EquipmentType $domainEntity): array;
}
