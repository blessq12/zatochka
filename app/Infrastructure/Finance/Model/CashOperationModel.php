<?php

namespace App\Infrastructure\Finance\Model;

use Illuminate\Database\Eloquent\Model;

final class CashOperationModel extends Model
{
    protected $table = 'cash_operations';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'type',
        'amount',
        'currency',
        'comment',
        'registered_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'registered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
