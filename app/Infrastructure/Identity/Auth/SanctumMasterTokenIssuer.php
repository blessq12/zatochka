<?php

namespace App\Infrastructure\Identity\Auth;

use App\Application\Identity\Port\MasterTokenIssuer;
use App\Infrastructure\Identity\Model\MasterModel;
use App\Shared\Domain\DomainException;

final class SanctumMasterTokenIssuer implements MasterTokenIssuer
{
    public function issueToken(int $masterId, string $tokenName = 'pos'): string
    {
        $master = MasterModel::query()->find($masterId);

        if ($master === null) {
            throw new DomainException('Master not found.');
        }

        return $master->createToken($tokenName)->plainTextToken;
    }
}
