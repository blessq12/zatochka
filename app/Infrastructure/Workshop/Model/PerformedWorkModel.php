<?php

namespace App\Infrastructure\Workshop\Model;

use Illuminate\Database\Eloquent\Model;

final class PerformedWorkModel extends Model
{
    protected $table = 'performed_works';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'production_task_id',
        'order_item_id',
        'equipment_component_id',
        'master_id',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
