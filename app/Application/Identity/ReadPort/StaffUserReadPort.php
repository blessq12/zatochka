<?php

namespace App\Application\Identity\ReadPort;

interface StaffUserReadPort
{
    public function existsAsMaster(int $userId): bool;
}
