<?php

namespace App\Infrastructure\Order\Model;

use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'tool_name',
        'tool_type',
        'quantity',
        'rejected_quantity',
        'rejection_reason',
        'status',
        'item_price_id',
        'warranty_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(ClientEquipmentModel::class, 'client_equipment_id');
    }

    public function reception(): HasOne
    {
        return $this->hasOne(ReceptionDataModel::class, 'order_item_id');
    }
}
