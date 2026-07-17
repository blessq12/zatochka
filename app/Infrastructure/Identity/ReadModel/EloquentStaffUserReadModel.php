<?php

namespace App\Infrastructure\Identity\ReadModel;

use App\Application\Identity\ReadPort\StaffUserReadPort;
use App\Domain\Identity\VO\StaffRole;
use App\Models\User;

final readonly class EloquentStaffUserReadModel implements StaffUserReadPort
{
    public function existsAsMaster(int $userId): bool
    {
        $user = User::query()->find($userId);

        return $user !== null && (string) $user->role->value === StaffRole::Master->value;
    }
}
