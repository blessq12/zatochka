<?php

namespace App\Infrastructure\Finance\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PaymentModel extends Model
{
    protected $table = 'payments';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'order_id',
        'amount',
        'currency',
        'method',
        'accepted_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'accepted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(RefundModel::class, 'payment_id');
    }
}
