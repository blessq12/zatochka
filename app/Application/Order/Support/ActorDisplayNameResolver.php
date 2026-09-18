<?php

namespace App\Application\Order\Support;

use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\ManagerRepository;
use App\Domain\Crm\Repository\MasterRepository;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;

final readonly class ActorDisplayNameResolver
{
    public function __construct(
        private ClientRepository $clients,
        private MasterRepository $masters,
        private ManagerRepository $managers,
        private ProfileAdditionalRepository $profiles,
    ) {}

    public function resolve(string $actorType, int $actorId): ?string
    {
        $actor = match ($actorType) {
            'clients' => $this->clients->findById($actorId),
            'masters' => $this->masters->findById($actorId),
            'managers' => $this->managers->findById($actorId),
            default => null,
        };

        if ($actor === null) {
            return null;
        }

        return $this->profiles->findById($actor->profileAdditionalId())?->name();
    }
}
