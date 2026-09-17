<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\ActorType;
use App\Domain\Crm\Aggregate\Actor;
use App\Domain\Crm\Aggregate\Client;
use App\Domain\Crm\Aggregate\Manager;
use App\Domain\Crm\Aggregate\Master;

final class ActorRepositoryResolver
{
    public function __construct(
        private ClientRepository $clients,
        private ManagerRepository $managers,
        private MasterRepository $masters,
    ) {}

    public function for(ActorType $type): ActorRepository
    {
        return match ($type) {
            ActorType::Client => $this->clients,
            ActorType::Manager => $this->managers,
            ActorType::Master => $this->masters,
        };
    }

    public function createActor(ActorType $type, int $profileAdditionalId): Actor
    {
        return match ($type) {
            ActorType::Client => Client::create($profileAdditionalId),
            ActorType::Manager => Manager::create($profileAdditionalId),
            ActorType::Master => Master::create($profileAdditionalId),
        };
    }
}
