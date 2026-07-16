<?php

namespace App\Application\Shared;

use App\Shared\ValueObject\EntityId;

interface EntityIdGenerator
{
    public function next(string $sequence): EntityId;
}
