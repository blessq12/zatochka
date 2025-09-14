<?php

namespace App\Domain\Notification\Mapper;

use App\Domain\Notification\Entity\Tool;

interface ToolMapper
{
    public function toDomain($eloquentModel): Tool;
    public function toEloquent(Tool $domainEntity): array;
}
