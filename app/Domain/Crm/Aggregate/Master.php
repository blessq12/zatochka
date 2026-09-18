<?php

namespace App\Domain\Crm\Aggregate;

use App\Domain\Crm\ActorType;

final class Master extends Actor
{
    public static function create(int $profileAdditionalId): self
    {
        return new self(null, $profileAdditionalId);
    }

    public function type(): ActorType
    {
        return ActorType::Master;
    }
}
