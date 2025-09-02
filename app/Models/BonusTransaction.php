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
}
