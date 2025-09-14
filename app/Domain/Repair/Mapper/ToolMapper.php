<?php

namespace App\Domain\Repair\Mapper;

use App\Domain\Repair\Entity\Tool;

interface ToolMapper
{
    public function toDomain($eloquentModel): Tool;
    public function toEloquent(Tool $domainEntity): array;
}
