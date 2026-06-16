<?php

namespace App\Infrastructure\Equipment\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

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
}
