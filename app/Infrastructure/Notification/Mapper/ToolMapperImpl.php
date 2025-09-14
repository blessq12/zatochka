<?php

namespace App\Infrastructure\Notification\Mapper;

use App\Domain\Notification\Entity\Tool;
use App\Domain\Notification\Mapper\ToolMapper;

class ToolMapperImpl implements ToolMapper
{
    public function toDomain($eloquentModel): Tool
    {
        // TODO: Implement toDomain logic
        return new Tool(id: $eloquentModel->id ?? null);
    }

    public function toEloquent(Tool $domainEntity): array
    {
        // TODO: Implement toEloquent logic
        return [];
    }
}
