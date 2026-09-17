<?php

namespace App\Application\Identity\Listener;

use App\Domain\Identity\Link\IdentityActorLink;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Shared\IntegrationEvents\ActorCreated;

final readonly class LinkIdentityOnActorCreated
{
    public function __construct(
        private IdentityActorLinkRepository $links,
    ) {}

    public function handle(ActorCreated $event): void
    {
        $this->links->save(new IdentityActorLink(
            $event->identityId,
            $event->actorType,
            $event->actorId,
        ));
    }
}
