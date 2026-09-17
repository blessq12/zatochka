<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Aggregate\ProfileAdditional;
use App\Domain\Crm\Repository\ActorRepositoryResolver;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use Illuminate\Support\Facades\DB;

final readonly class CreateActorHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
        private ProfileAdditionalRepository $profiles,
    ) {}

    public function handle(ActorType $type): ActorResponse
    {
        return DB::transaction(function () use ($type): ActorResponse {
            $profile = $this->profiles->save(ProfileAdditional::create());

            $actor = $this->actors->createActor($type, (int) $profile->id());
            $actor = $this->actors->for($type)->save($actor);

            return new ActorResponse((int) $actor->id(), $actor->profileAdditionalId());
        });
    }
}
