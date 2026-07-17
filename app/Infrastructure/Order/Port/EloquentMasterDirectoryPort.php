<?php

namespace App\Infrastructure\Order\Port;

use App\Application\Order\Port\MasterDirectoryPort;
use App\Models\User;
use App\Models\UserRole;

final readonly class EloquentMasterDirectoryPort implements MasterDirectoryPort
{
    public function existsAsMaster(int $userId): bool
    {
        $user = User::query()->find($userId);

        return $user !== null && $user->role === UserRole::Master;
    }
}
