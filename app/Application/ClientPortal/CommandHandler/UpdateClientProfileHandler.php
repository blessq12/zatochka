<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\UpdateClientProfileCommand;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Domain\ClientPortal\Entity\Client;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;

final class UpdateClientProfileHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
        private ClientRepositoryInterface $clients,
    ) {}

    public function handle(UpdateClientProfileCommand $command): Client
    {
        $client = $this->clientLoader->load($command->clientId);

        $updated = $client->updateProfile(
            fullName: $command->fullName,
            email: $command->email,
            birthDate: $command->birthDate,
            deliveryAddress: $command->deliveryAddress,
        );

        return $this->clients->save($updated);
    }
}
