<?php

namespace App\Infrastructure\Equipment\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ClientEquipmentModel extends Model
{
    protected $table = 'client_equipment';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'client_id', 'title', 'brand', 'model_name', 'equipment_type', 'notes'];

    public function components(): HasMany
    {
        return $this->hasMany(EquipmentComponentModel::class, 'equipment_id');
    }

    public function repairHistory(): HasMany
    {
        return $this->hasMany(RepairHistoryModel::class, 'equipment_id');
    }
}
