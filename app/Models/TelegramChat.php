<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'username',
        'chat_id',
        'is_active',
        'metadata',
        'is_deleted',
    ];

    protected $casts = [
        'chat_id' => 'integer',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Связь с клиентом
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Скоуп для активных чатов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    /**
     * Найти чат по chat_id
     */
    public function scopeByChatId($query, int $chatId)
    {
        return $query->where('chat_id', $chatId);
    }

    /**
     * Найти чат по username
     */
    public function scopeByUsername($query, string $username)
    {
        return $query->where('username', $username);
    }
}
