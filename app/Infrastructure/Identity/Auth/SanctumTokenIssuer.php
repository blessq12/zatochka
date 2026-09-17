<?php

namespace App\Infrastructure\Identity\Auth;

use App\Application\Identity\Port\TokenIssuer;
use App\Infrastructure\Identity\Eloquent\IdentityModel;

final class SanctumTokenIssuer implements TokenIssuer
{
    public function issue(int $identityId, string $tokenName = 'api'): string
    {
        /** @var IdentityModel $model */
        $model = IdentityModel::query()->findOrFail($identityId);

        return $model->createToken($tokenName)->plainTextToken;
    }
}
