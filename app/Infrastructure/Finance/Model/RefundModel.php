<?php

namespace App\Infrastructure\Finance\Model;

use Illuminate\Database\Eloquent\Model;

final class RefundModel extends Model
{
    protected $table = 'refunds';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'payment_id',
        'amount',
        'currency',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'created_at' => 'datetime',
        ];
    }
}
