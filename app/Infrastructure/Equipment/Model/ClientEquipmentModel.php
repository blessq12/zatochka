<?php

namespace App\Infrastructure\Equipment\Model;

use App\Infrastructure\CRM\Model\ClientModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ClientEquipmentModel extends Model
{
    protected $table = 'client_equipment';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'number', 'client_id', 'title', 'brand', 'model_name', 'equipment_type'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(EquipmentComponentModel::class, 'equipment_id');
    }

    public function repairHistory(): HasMany
    {
        return $this->hasMany(RepairHistoryModel::class, 'equipment_id');
    }
}
