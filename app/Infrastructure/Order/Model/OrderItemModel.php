<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class OrderItemModel extends Model
{
    protected $table = 'order_items';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'order_id',
        'client_equipment_id',
        'status',
        'production_task_id',
        'item_price_id',
        'warranty_id',
    ];

    public function reception(): HasOne
    {
        return $this->hasOne(ReceptionDataModel::class, 'order_item_id');
    }
}
