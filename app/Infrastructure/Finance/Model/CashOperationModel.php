<?php

namespace App\Infrastructure\Finance\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashOperationModel extends Model
{
    protected $table = 'cash_operations';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'type',
        'payment_method',
        'amount',
        'currency',
        'comment',
        'payment_id',
        'refund_id',
        'registered_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'payment_id' => 'integer',
            'refund_id' => 'integer',
            'registered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentModel::class, 'payment_id');
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(RefundModel::class, 'refund_id');
    }
}
