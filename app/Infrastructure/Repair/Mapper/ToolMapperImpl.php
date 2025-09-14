<?php

namespace App\Infrastructure\Repair\Mapper;

use App\Domain\Repair\Entity\Tool;
use App\Domain\Repair\Mapper\ToolMapper;

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
