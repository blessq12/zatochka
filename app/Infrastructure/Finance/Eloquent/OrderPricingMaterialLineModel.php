<?php

namespace App\Infrastructure\Finance\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderPricingMaterialLineModel extends Model
{
    protected $table = 'order_pricing_material_lines';

    protected $fillable = [
        'pricing_id',
        'stock_item_id',
        'amount',
    ];

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(OrderPricingModel::class, 'pricing_id');
    }
}
