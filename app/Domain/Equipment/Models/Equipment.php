<?php

namespace App\Domain\Equipment\Models;

use App\Domain\OrderFulfillment\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
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
        return $this->hasMany(Order::class, 'equipment_id');
    }
}
