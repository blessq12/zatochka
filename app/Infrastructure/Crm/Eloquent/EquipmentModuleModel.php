<?php

namespace App\Infrastructure\Crm\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EquipmentModuleModel extends Model
{
    protected $table = 'equipment_modules';

    protected $fillable = [
        'equipment_id',
        'name',
        'serial_number',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id');
    }
}
