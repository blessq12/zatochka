<?php

namespace App\Application\Crm\Command;

use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ActorRepositoryResolver;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\ActorSoftDeleted;
use Illuminate\Support\Facades\DB;

final readonly class DeleteActorHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
        private ProfileAdditionalRepository $profiles,
        private EventBus $events,
    ) {}

    public function handle(ActorType $type, int $id): bool
    {
        return DB::transaction(function () use ($type, $id): bool {
            $repository = $this->actors->for($type);
            $actor = $repository->findById($id);

            if ($actor === null) {
                return false;
            }

            $profile = $this->profiles->findById($actor->profileAdditionalId());

            $repository->delete($actor);

            if ($profile !== null) {
                $this->profiles->delete($profile);
            }

            $this->events->publish(new ActorSoftDeleted($type->value, $id));

            return true;
        });
    }
}
