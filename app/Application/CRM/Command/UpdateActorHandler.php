<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ActorRepositoryResolver;

final readonly class UpdateActorHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
    ) {}

    public function handle(ActorType $type, int $id): ?ActorResponse
    {
        $repository = $this->actors->for($type);
        $actor = $repository->findById($id);

        if ($actor === null) {
            return null;
        }

        $actor = $repository->save($actor);

        return new ActorResponse((int) $actor->id(), $actor->profileAdditionalId());
    }
}
