<?php

namespace App\Domain\Identity\Link;

interface IdentityActorLinkRepository
{
    public function save(IdentityActorLink $link): void;

    public function findByIdentityId(int $identityId): ?IdentityActorLink;

    public function findByActor(string $actorType, int $actorId): ?IdentityActorLink;

    public function deleteByActor(string $actorType, int $actorId): ?int;
}
