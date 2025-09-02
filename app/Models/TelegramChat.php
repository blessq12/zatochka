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
        'metadata' => 'array',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function messages()
    {
        return $this->hasMany(TelegramMessage::class, 'chat_id');
    }

    // Scope для активных чатов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }
}
