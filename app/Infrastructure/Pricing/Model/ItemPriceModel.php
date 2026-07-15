<?php

namespace App\Infrastructure\Pricing\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ItemPriceModel extends Model
{
    protected $table = 'item_prices';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'estimate_id',
        'order_item_id',
        'base_amount',
        'currency',
        'final_amount',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'string',
            'final_amount' => 'string',
        ];
    }

    public function discount(): HasOne
    {
        return $this->hasOne(DiscountModel::class, 'item_price_id');
    }
}
