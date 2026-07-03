<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\RegisterClientCommand;
use App\Domain\ClientPortal\Entity\Client;
use App\Domain\ClientPortal\Event\ClientRegistered;
use App\Domain\ClientPortal\Exception\ClientAlreadyRegisteredException;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final class RegisterClientHandler
{
    public function __construct(
        private ClientRepositoryInterface $clients,
    ) {}

    public function handle(RegisterClientCommand $command): Client
    {
        if ($this->clients->findByPhone($command->phone) !== null) {
            throw ClientAlreadyRegisteredException::forPhone($command->phone);
        }

        $client = Client::register(
            phone: $command->phone,
            fullName: $command->fullName,
            email: $command->email,
            birthDate: $command->birthDate,
            deliveryAddress: $command->deliveryAddress,
        );

        $saved = $this->clients->save($client, Hash::make($command->password));

        event(new ClientRegistered($saved));

        return $saved;
    }
}
