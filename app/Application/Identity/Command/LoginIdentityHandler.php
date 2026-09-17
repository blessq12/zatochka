<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\DTO\ActorSummary;
use App\Application\Identity\DTO\IdentityResponse;
use App\Application\Identity\Port\TokenIssuer;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Shared\Domain\DomainException;
use App\Shared\Domain\ForbiddenException;
use Illuminate\Support\Facades\Hash;

final readonly class LoginIdentityHandler
{
    public function __construct(
        private IdentityRepository $identities,
        private IdentityActorLinkRepository $links,
        private TokenIssuer $tokens,
    ) {}

    public function handle(string $email, string $password, ?string $expectedActorType = null): IdentityResponse
    {
        $identity = $this->identities->findByEmail($email);

        if ($identity === null || ! Hash::check($password, $identity->passwordHash())) {
            throw new DomainException('Invalid credentials.');
        }

        $link = $this->links->findByIdentityId((int) $identity->id());

        if ($link === null) {
            throw new DomainException('Identity is not linked to an actor.');
        }

        if ($expectedActorType !== null && $link->actorType !== $expectedActorType) {
            throw new ForbiddenException('Forbidden for this actor type.');
        }

        $token = $this->tokens->issue((int) $identity->id());

        return new IdentityResponse(
            (int) $identity->id(),
            $identity->email(),
            new ActorSummary($link->actorType, $link->actorId),
            $token,
        );
    }
}
