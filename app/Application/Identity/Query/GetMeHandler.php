<?php

namespace App\Application\Identity\Query;

use App\Application\Identity\DTO\ActorSummary;
use App\Application\Identity\DTO\IdentityResponse;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Shared\Domain\DomainException;

final readonly class GetMeHandler
{
    public function __construct(
        private IdentityActorLinkRepository $links,
    ) {}

    public function handle(int $identityId, string $email): IdentityResponse
    {
        $link = $this->links->findByIdentityId($identityId);

        if ($link === null) {
            throw new DomainException('Identity is not linked to an actor.');
        }

        return new IdentityResponse(
            $identityId,
            $email,
            new ActorSummary($link->actorType, $link->actorId),
        );
    }
}
