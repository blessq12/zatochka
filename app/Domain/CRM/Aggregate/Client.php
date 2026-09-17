<?php

namespace App\Domain\Crm\Aggregate;

use App\Domain\Crm\ActorType;

final class Client extends Actor
{
    public static function create(int $profileAdditionalId): self
    {
        return new self(null, $profileAdditionalId);
    }

    public function type(): ActorType
    {
        return ActorType::Client;
    }
}
