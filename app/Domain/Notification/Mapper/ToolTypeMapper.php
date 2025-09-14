<?php

namespace App\Domain\Notification\Mapper;

use App\Domain\Notification\Entity\ToolType;

interface ToolTypeMapper
{
    public function toDomain($eloquentModel): ToolType;
    public function toEloquent(ToolType $domainEntity): array;
}
