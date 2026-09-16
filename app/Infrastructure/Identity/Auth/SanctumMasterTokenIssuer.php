<?php

namespace App\Infrastructure\Identity\Auth;

use App\Application\Identity\Port\MasterTokenIssuer;
use App\Infrastructure\Identity\Model\MasterAccountModel;
use App\Shared\Domain\DomainException;

final class SanctumMasterTokenIssuer implements MasterTokenIssuer
{
    public function issueToken(int $masterId, string $tokenName = 'pos'): string
    {
        $account = MasterAccountModel::query()->find($masterId);

        if ($account === null) {
            throw new DomainException('Master account not found.');
        }

        return $account->createToken($tokenName)->plainTextToken;
    }
}
