<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\DTO\ActorSummary;
use App\Application\Identity\DTO\IdentityResponse;
use App\Application\Identity\Port\TokenIssuer;
use App\Domain\Identity\Aggregate\Identity;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Shared\Domain\DomainException;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\ActorRegistrationRequested;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class RegisterActorWithIdentityHandler
{
    public function __construct(
        private IdentityRepository $identities,
        private IdentityActorLinkRepository $links,
        private EventBus $events,
        private TokenIssuer $tokens,
    ) {}

    public function handle(string $actorType, string $email, string $password, bool $issueToken = false): IdentityResponse
    {
        if ($this->identities->findByEmail($email) !== null) {
            throw new DomainException('Email already taken.');
        }

        return DB::transaction(function () use ($actorType, $email, $password, $issueToken): IdentityResponse {
            $identity = $this->identities->save(
                Identity::create($email, Hash::make($password))
            );

            $this->events->publish(new ActorRegistrationRequested(
                (int) $identity->id(),
                $actorType,
            ));

            $link = $this->links->findByIdentityId((int) $identity->id());

            if ($link === null) {
                throw new DomainException('Actor was not created for identity.');
            }

            $token = $issueToken
                ? $this->tokens->issue((int) $identity->id())
                : null;

            return new IdentityResponse(
                (int) $identity->id(),
                $identity->email(),
                new ActorSummary($link->actorType, $link->actorId),
                $token,
            );
        });
    }
}
