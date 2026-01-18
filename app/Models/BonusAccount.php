<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(BonusTransaction::class, 'client_id', 'client_id');
    }

    /**
     * Получить или создать бонусный счет для клиента
     */
    public static function getOrCreateForClient(Client $client): self
    {
        return static::firstOrCreate(
            ['client_id' => $client->id],
            ['balance' => 0]
        );
    }

    /**
     * Начислить бонусы
     */
    public function earn(int $amount, ?Order $order = null, ?string $description = null): BonusTransaction
    {
        $this->increment('balance', $amount);

        return BonusTransaction::create([
            'client_id' => $this->client_id,
            'order_id' => $order?->id,
            'type' => 'earn',
            'amount' => $amount,
            'description' => $description ?? 'Начисление бонусов',
        ]);
    }

    /**
     * Списать бонусы
     */
    public function spend(int $amount, ?Order $order = null, ?string $description = null): ?BonusTransaction
    {
        if ($this->balance < $amount) {
            return null; // Недостаточно бонусов
        }

        $this->decrement('balance', $amount);

        return BonusTransaction::create([
            'client_id' => $this->client_id,
            'order_id' => $order?->id,
            'type' => 'spend',
            'amount' => $amount,
            'description' => $description ?? 'Списание бонусов',
        ]);
    }
}
