<?php

namespace App\Infrastructure\Identity\ReadModel;

use App\Application\Identity\ReadPort\StaffUserReadPort;
use App\Infrastructure\Identity\Model\MasterModel;

final class EloquentStaffUserReadModel implements StaffUserReadPort
{
    public function existsAsMaster(int $userId): bool
    {
        return MasterModel::query()->whereKey($userId)->exists();
    }
}
