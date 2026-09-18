<?php

namespace App\Infrastructure\Crm\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EquipmentModel extends Model
{
    use SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'client_id',
        'name',
        'brand',
        'type',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(EquipmentModuleModel::class, 'equipment_id');
    }
}
