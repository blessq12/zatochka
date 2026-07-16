<?php

namespace App\Infrastructure\Pricing\Model;

use Illuminate\Database\Eloquent\Model;

final class WorkPriceModel extends Model
{
    protected $table = 'work_prices';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'performed_work_id',
        'order_item_id',
        'base_amount',
        'currency',
        'final_amount',
        'calculated',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'string',
            'final_amount' => 'string',
            'calculated' => 'boolean',
        ];
    }
}
