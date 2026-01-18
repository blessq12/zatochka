<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_id',
        'type',
        'amount',
        'description',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scope для начислений
    public function scopeEarned($query)
    {
        return $query->where('type', 'earn');
    }

    // Scope для списаний
    public function scopeSpent($query)
    {
        return $query->where('type', 'spend');
    }
}
