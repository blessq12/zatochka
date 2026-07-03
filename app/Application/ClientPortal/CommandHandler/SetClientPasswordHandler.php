<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\SetClientPasswordCommand;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Domain\ClientPortal\Entity\Client;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final class SetClientPasswordHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
        private ClientRepositoryInterface $clients,
    ) {}

    public function handle(SetClientPasswordCommand $command): Client
    {
        $client = $this->clientLoader->load($command->clientId);

        return $this->clients->save(
            $client->markPasswordSet(),
            Hash::make($command->password),
        );
    }
}
