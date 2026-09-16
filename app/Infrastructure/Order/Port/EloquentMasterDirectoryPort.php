<?php

namespace App\Infrastructure\Order\Port;

use App\Application\CRM\ReadPort\StaffDirectoryReadPort;
use App\Application\Order\Port\MasterDirectoryPort;

final readonly class EloquentMasterDirectoryPort implements MasterDirectoryPort
{
    public function __construct(
        private StaffDirectoryReadPort $staffDirectory,
    ) {}

    public function existsAsMaster(int $userId): bool
    {
        return $this->staffDirectory->existsAsMaster($userId);
    }
}
