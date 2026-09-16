<?php

namespace App\Application\Identity\Port;

interface StaffPersonReadPort
{
    /**
     * @return array{name: string, email: string}|null
     */
    public function findManager(int $managerId): ?array;

    /**
     * @return array{name: string, email: string}|null
     */
    public function findMaster(int $masterId): ?array;
}
