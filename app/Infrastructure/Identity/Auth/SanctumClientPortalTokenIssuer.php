<?php

namespace App\Infrastructure\Identity\Auth;

use App\Application\CRM\Port\ClientPortalTokenIssuer;
use App\Infrastructure\Identity\Model\ClientAccountModel;
use App\Shared\Domain\DomainException;

final class SanctumClientPortalTokenIssuer implements ClientPortalTokenIssuer
{
    public function issueToken(int $clientId, string $tokenName = 'client-portal'): string
    {
        $account = ClientAccountModel::query()->where('client_id', $clientId)->first();

        if ($account === null) {
            throw new DomainException('Client account not found.');
        }

        return $account->createToken($tokenName)->plainTextToken;
    }
}
