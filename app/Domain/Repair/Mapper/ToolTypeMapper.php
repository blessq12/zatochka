<?php

namespace App\Domain\Repair\Mapper;

use App\Domain\Repair\Entity\ToolType;

interface ToolTypeMapper
{
    public function toDomain($eloquentModel): ToolType;
    public function toEloquent(ToolType $domainEntity): array;
}
