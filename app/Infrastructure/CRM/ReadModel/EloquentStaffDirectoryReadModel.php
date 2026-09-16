<?php

namespace App\Infrastructure\CRM\ReadModel;

use App\Application\CRM\ReadPort\StaffDirectoryReadPort;
use App\Infrastructure\CRM\Model\MasterModel;

final class EloquentStaffDirectoryReadModel implements StaffDirectoryReadPort
{
    public function existsAsMaster(int $userId): bool
    {
        return MasterModel::query()->whereKey($userId)->exists();
    }
}
