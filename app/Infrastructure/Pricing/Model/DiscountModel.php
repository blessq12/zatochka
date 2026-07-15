<?php

namespace App\Infrastructure\Pricing\Model;

use Illuminate\Database\Eloquent\Model;

final class DiscountModel extends Model
{
    protected $table = 'discounts';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'item_price_id',
        'type',
        'value',
        'reason',
    ];

    protected function casts(): array
    {
        return ['value' => 'string'];
    }
}
