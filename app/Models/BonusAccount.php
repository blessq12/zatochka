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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
