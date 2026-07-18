<?php

namespace App\Infrastructure\CRM\Auth;

use App\Application\CRM\Port\ClientPortalTokenIssuer;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\Domain\DomainException;

final class SanctumClientPortalTokenIssuer implements ClientPortalTokenIssuer
{
    public function issueToken(int $clientId, string $tokenName = 'client-portal'): string
    {
        $client = ClientModel::query()->find($clientId);

        if ($client === null) {
            throw new DomainException('Client not found.');
        }

        return $client->createToken($tokenName)->plainTextToken;
    }
}
