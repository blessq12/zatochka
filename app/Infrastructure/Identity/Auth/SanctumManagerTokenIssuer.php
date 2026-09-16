<?php

namespace App\Infrastructure\Identity\Auth;

use App\Application\Identity\Port\ManagerTokenIssuer;
use App\Infrastructure\Identity\Model\ManagerAccountModel;
use App\Shared\Domain\DomainException;

final class SanctumManagerTokenIssuer implements ManagerTokenIssuer
{
    public function issueToken(int $managerId, string $tokenName = 'manager'): string
    {
        $account = ManagerAccountModel::query()->find($managerId);

        if ($account === null) {
            throw new DomainException('Manager account not found.');
        }

        return $account->createToken($tokenName)->plainTextToken;
    }
}
