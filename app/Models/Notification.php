<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'channel',
        'content',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Scope для уведомлений по статусу
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope для уведомлений по каналу
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }
}
