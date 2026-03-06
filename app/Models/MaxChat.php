<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaxChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'username',
        'user_id',
        'is_active',
        'metadata',
        'is_deleted',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'metadata' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    public function scopeByUserId($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByUsername($query, string $username)
    {
        return $query->where('username', $username);
    }
}
