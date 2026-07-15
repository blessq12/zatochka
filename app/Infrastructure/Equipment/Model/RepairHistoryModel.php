<?php

namespace App\Infrastructure\Equipment\Model;

use Illuminate\Database\Eloquent\Model;

final class RepairHistoryModel extends Model
{
    protected $table = 'repair_history';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'equipment_id', 'order_item_id', 'summary', 'recorded_at'];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }
}
