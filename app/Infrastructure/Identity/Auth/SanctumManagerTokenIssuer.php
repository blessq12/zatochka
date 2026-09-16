<?php

namespace App\Infrastructure\Identity\Auth;

use App\Application\Identity\Port\ManagerTokenIssuer;
use App\Infrastructure\Identity\Model\ManagerModel;
use App\Shared\Domain\DomainException;

final class SanctumManagerTokenIssuer implements ManagerTokenIssuer
{
    public function issueToken(int $managerId, string $tokenName = 'manager'): string
    {
        $manager = ManagerModel::query()->find($managerId);

        if ($manager === null) {
            throw new DomainException('Manager not found.');
        }

        return $manager->createToken($tokenName)->plainTextToken;
    }
}
