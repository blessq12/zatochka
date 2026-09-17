<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ActorRepositoryResolver;

final readonly class GetActorHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
    ) {}

    public function handle(ActorType $type, int $id): ?ActorResponse
    {
        $actor = $this->actors->for($type)->findById($id);

        if ($actor === null) {
            return null;
        }

        return new ActorResponse((int) $actor->id(), $actor->profileAdditionalId());
    }
}
