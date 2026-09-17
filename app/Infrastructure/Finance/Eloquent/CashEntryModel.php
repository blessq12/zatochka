<?php

namespace App\Infrastructure\Finance\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class CashEntryModel extends Model
{
    protected $table = 'cash_entries';

    protected $fillable = [
        'type',
        'amount',
        'occurred_at',
        'source',
        'order_id',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }
}
