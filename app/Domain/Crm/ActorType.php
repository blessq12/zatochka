<?php

namespace App\Domain\Crm;

use App\Shared\Domain\DomainException;

enum ActorType: string
{
    case Client = 'clients';
    case Manager = 'managers';
    case Master = 'masters';

    public static function fromRoute(string $type): self
    {
        $actorType = self::tryFrom($type);

        if ($actorType === null) {
            throw new DomainException('Unknown actor type.');
        }

        return $actorType;
    }
}
