<?php

namespace App\Infrastructure\Notification\Mapper;

use App\Domain\Notification\Entity\ToolType;
use App\Domain\Notification\Mapper\ToolTypeMapper;

class ToolTypeMapperImpl implements ToolTypeMapper
{
    public function toDomain($eloquentModel): ToolType
    {
        // TODO: Implement toDomain logic
        return new ToolType(id: $eloquentModel->id ?? null);
    }

    public function toEloquent(ToolType $domainEntity): array
    {
        // TODO: Implement toEloquent logic
        return [];
    }
}
