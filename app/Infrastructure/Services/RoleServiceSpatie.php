<?php

namespace App\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use App\Domain\Shared\Interfaces\RoleServiceInterface;
use App\Models\User;

class RoleServiceSpatie implements RoleServiceInterface
{
    public function assignRoles(int $userId, array $roles): void
    {
        $user = User::query()->where('id', $userId)->firstOrFail();
        $user->syncRoles($roles);
    }

    public function getRoles(int $userId): array
    {
        $user = User::query()->where('id', $userId)->firstOrFail();
        return $user->getRoleNames()->toArray();
    }
}
