<?php

namespace App\Infrastructure\Equipment\Persistence\Eloquent;

use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentModel extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'brand',
        'model',
        'serial_numbers',
    ];

    protected function casts(): array
    {
        return [
            'serial_numbers' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class, 'equipment_id')->orderByDesc('created_at');
    }
}
