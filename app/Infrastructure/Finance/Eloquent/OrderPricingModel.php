<?php

namespace App\Infrastructure\Finance\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderPricingModel extends Model
{
    protected $table = 'order_pricings';

    protected $fillable = [
        'order_id',
        'status',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(OrderPricingLineModel::class, 'pricing_id')->orderBy('id');
    }

    public function materialLines(): HasMany
    {
        return $this->hasMany(OrderPricingMaterialLineModel::class, 'pricing_id')->orderBy('id');
    }
}
