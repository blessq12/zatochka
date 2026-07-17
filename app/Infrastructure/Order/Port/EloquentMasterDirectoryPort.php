<?php

namespace App\Infrastructure\Order\Port;

use App\Application\Identity\ReadPort\StaffUserReadPort;
use App\Application\Order\Port\MasterDirectoryPort;

final readonly class EloquentMasterDirectoryPort implements MasterDirectoryPort
{
    public function __construct(
        private StaffUserReadPort $staffUsers,
    ) {}

    public function existsAsMaster(int $userId): bool
    {
        return $this->staffUsers->existsAsMaster($userId);
    }
}
