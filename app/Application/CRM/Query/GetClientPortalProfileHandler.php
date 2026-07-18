<?php

namespace App\Application\CRM\Query;

use App\Application\CRM\DTO\ClientPortalProfileDTO;
use App\Application\CRM\ReadPort\ClientReadPort;
use App\Models\User;
use App\Models\UserRole;

final readonly class GetClientPortalProfileHandler
{
    public function __construct(
        private ClientReadPort $clients,
    ) {}

    public function handle(int $clientId, ?User $portalUser = null): ?ClientPortalProfileDTO
    {
        $client = $this->clients->findById($clientId);

        if ($client === null) {
            return null;
        }

        $requiresPasswordSet = false;
        if ($portalUser !== null && $portalUser->role === UserRole::Client) {
            $requiresPasswordSet = (bool) $portalUser->requires_password_set;
        }

        return new ClientPortalProfileDTO(
            $client->id,
            $client->name,
            $client->phone,
            $client->email,
            $client->birthDate,
            $client->deliveryAddress,
            $client->bonusBalance,
            $requiresPasswordSet,
        );
    }
}
