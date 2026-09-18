<?php

namespace App\Application\Identity\Listener;

use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Shared\IntegrationEvents\ActorSoftDeleted;

final readonly class UnlinkAndDeleteIdentityOnActorSoftDeleted
{
    public function __construct(
        private IdentityActorLinkRepository $links,
        private IdentityRepository $identities,
    ) {}

    public function handle(ActorSoftDeleted $event): void
    {
        $identityId = $this->links->deleteByActor($event->actorType, $event->actorId);

        if ($identityId !== null) {
            $this->identities->deleteById($identityId);
        }
    }
}
