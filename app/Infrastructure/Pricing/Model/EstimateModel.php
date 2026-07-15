<?php

namespace App\Infrastructure\Pricing\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class EstimateModel extends Model
{
    protected $table = 'estimates';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'order_item_id',
        'estimated_amount',
        'currency',
        'calculated',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'string',
            'calculated' => 'boolean',
        ];
    }

    public function itemPrice(): HasOne
    {
        return $this->hasOne(ItemPriceModel::class, 'estimate_id');
    }
}
