<?php

namespace App\Infrastructure\Finance\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class EarningsGoalModel extends Model
{
    protected $table = 'earnings_goals';

    protected $fillable = [
        'title',
        'target_amount',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'datetime',
        ];
    }
}
