<?php

namespace App\Infrastructure\Workshop\Port;

use App\Application\Workshop\Port\EquipmentComponentBelongingPort;
use Illuminate\Support\Facades\DB;

final readonly class EloquentEquipmentComponentBelongingPort implements EquipmentComponentBelongingPort
{
    public function belongsToEquipment(int $equipmentId, int $componentId): bool
    {
        return DB::table('equipment_components')
            ->where('id', $componentId)
            ->where('equipment_id', $equipmentId)
            ->exists();
    }
}
