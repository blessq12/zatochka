<?php

namespace App\Application\CRM\ReadPort;

interface StaffDirectoryReadPort
{
    public function existsAsMaster(int $userId): bool;
}
