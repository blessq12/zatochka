<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'client_id',
        'content',
        'direction',
        'sent_at',
        'is_deleted',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function chat()
    {
        return $this->belongsTo(TelegramChat::class, 'chat_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Scope для активных сообщений
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для входящих сообщений
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    // Scope для исходящих сообщений
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }
}
