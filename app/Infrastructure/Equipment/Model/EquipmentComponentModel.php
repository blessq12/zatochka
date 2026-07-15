<?php

namespace App\Infrastructure\Equipment\Model;

use Illuminate\Database\Eloquent\Model;

final class EquipmentComponentModel extends Model
{
    protected $table = 'equipment_components';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'equipment_id', 'name', 'serial_number'];
}
