<?php

namespace App\Application\Crm\Port;

interface ActorEmailResolver
{
    public function resolve(string $actorType, int $actorId): ?string;
}
