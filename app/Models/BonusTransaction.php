<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusTransaction extends Model
{
    protected $fillable = [
        'client_id',
        'order_id',
        'type',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isEarn(): bool
    {
        return $this->type === 'earn';
    }

    public function isSpend(): bool
    {
        return $this->type === 'spend';
    }

    public static function createEarn(int $clientId, float $amount, string $description, ?int $orderId = null): self
    {
        return self::create([
            'client_id' => $clientId,
            'order_id' => $orderId,
            'type' => 'earn',
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public static function createSpend(int $clientId, float $amount, string $description, ?int $orderId = null): self
    {
        return self::create([
            'client_id' => $clientId,
            'order_id' => $orderId,
            'type' => 'spend',
            'amount' => $amount,
            'description' => $description,
        ]);
    }
}
