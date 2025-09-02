<?php

namespace App\Infrastructure\Services;

use Illuminate\Support\Facades\Log;

use App\Domain\Shared\Interfaces\RoleServiceInterface;
use App\Domain\Users\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class RoleServiceSpatie implements RoleServiceInterface
{
    public function assignRoles(UserId $userId, array $roles): void
    {
        $user = UserModel::query()->where('uuid', (string) $userId)->firstOrFail();
        $user->syncRoles($roles);
    }

    public function getRoles(UserId $userId): array
    {
        $user = UserModel::query()->where('uuid', (string) $userId)->firstOrFail();
        return $user->getRoleNames()->toArray();
    }
}
