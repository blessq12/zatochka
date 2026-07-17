<?php

namespace App\Application\Order\Port;

interface MasterDirectoryPort
{
    public function existsAsMaster(int $userId): bool;
}
