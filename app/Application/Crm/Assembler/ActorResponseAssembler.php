<?php

namespace App\Application\Crm\Assembler;

use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Aggregate\Actor;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;

final readonly class ActorResponseAssembler
{
    public function __construct(
        private ProfileAdditionalRepository $profiles,
    ) {}

    public function assemble(ActorType $type, Actor $actor): ActorResponse
    {
        $profile = $this->profiles->findById($actor->profileAdditionalId());

        return new ActorResponse(
            (int) $actor->id(),
            $type->value,
            $profile?->email(),
            $actor->profileAdditionalId(),
            $profile?->name(),
            $profile?->phone(),
            $profile?->birthday()?->format('Y-m-d'),
            $profile?->deliveryAddress(),
        );
    }
}
