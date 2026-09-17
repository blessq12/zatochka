<?php

namespace App\Application\Crm\Assembler;

use App\Application\Crm\DTO\ActorResponse;
use App\Application\Crm\Port\ActorEmailResolver;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Aggregate\Actor;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;

final readonly class ActorResponseAssembler
{
    public function __construct(
        private ProfileAdditionalRepository $profiles,
        private ActorEmailResolver $emails,
    ) {}

    public function assemble(ActorType $type, Actor $actor): ActorResponse
    {
        $profile = $this->profiles->findById($actor->profileAdditionalId());

        return new ActorResponse(
            (int) $actor->id(),
            $type->value,
            $this->emails->resolve($type->value, (int) $actor->id()),
            $actor->profileAdditionalId(),
            $profile?->name(),
            $profile?->phone(),
            $profile?->birthday()?->format('Y-m-d'),
        );
    }
}
