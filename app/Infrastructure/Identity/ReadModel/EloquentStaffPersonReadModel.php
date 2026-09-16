<?php

namespace App\Infrastructure\Identity\ReadModel;

use App\Application\Identity\Port\StaffPersonReadPort;
use App\Infrastructure\CRM\Model\ManagerModel;
use App\Infrastructure\CRM\Model\MasterModel;

final class EloquentStaffPersonReadModel implements StaffPersonReadPort
{
    public function findManager(int $managerId): ?array
    {
        $person = ManagerModel::query()->find($managerId);

        if ($person === null) {
            return null;
        }

        return [
            'name' => (string) $person->name,
            'email' => (string) $person->email,
        ];
    }

    public function findMaster(int $masterId): ?array
    {
        $person = MasterModel::query()->find($masterId);

        if ($person === null) {
            return null;
        }

        return [
            'name' => (string) $person->name,
            'email' => (string) $person->email,
        ];
    }
}
