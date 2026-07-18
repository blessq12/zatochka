<?php

namespace App\Application\CRM\Query;

use App\Application\CRM\DTO\ClientPortalProfileDTO;
use App\Application\CRM\ReadPort\ClientReadPort;

final readonly class GetClientPortalProfileHandler
{
    public function __construct(
        private ClientReadPort $clients,
    ) {}

    public function handle(int $clientId): ?ClientPortalProfileDTO
    {
        $client = $this->clients->findById($clientId);

        if ($client === null) {
            return null;
        }

        return new ClientPortalProfileDTO(
            $client->id,
            $client->name,
            $client->phone,
            $client->email,
            $client->birthDate,
            $client->deliveryAddress,
            $client->bonusBalance,
            false,
        );
    }
}
