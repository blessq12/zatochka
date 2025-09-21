<?php

namespace App\Domain\Repair\Mapper;

use App\Domain\Repair\Entity\Repair;
use App\Models\Repair as RepairModel;

interface RepairMapper
{
    public function toDomain(RepairModel $model): Repair;
    public function toEloquent(Repair $repair): RepairModel;
}
