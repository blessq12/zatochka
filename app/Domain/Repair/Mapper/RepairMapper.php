<?php

namespace App\Domain\Repair\Mapper;

use App\Domain\Repair\Entity\Repair;

interface RepairMapper
{
    public function toDomain($eloquentModel): Repair;
    public function toEloquent(Repair $domainEntity): array;
}
