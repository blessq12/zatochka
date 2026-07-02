<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\CreateClientCommand;
use App\Domain\ClientPortal\Entity\Client;
use App\Domain\ClientPortal\Event\ClientRegistered;
use App\Domain\ClientPortal\Exception\ClientAlreadyRegisteredException;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CreateClientHandler
{
    public function __construct(
        private ClientRepositoryInterface $clients,
    ) {}

    public function handle(CreateClientCommand $command): Client
    {
        if ($this->clients->findByPhone($command->phone) !== null) {
            throw ClientAlreadyRegisteredException::forPhone($command->phone);
        }

        $client = Client::createByManager(
            phone: $command->phone,
            fullName: $command->fullName,
            email: $command->email,
            birthDate: $command->birthDate,
            deliveryAddress: $command->deliveryAddress,
        );

        $saved = $this->clients->save($client, Hash::make(Str::password(32)));

        event(new ClientRegistered($saved));

        return $saved;
    }
}
