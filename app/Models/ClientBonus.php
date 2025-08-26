<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ClientBonus extends Model
{
    protected $fillable = [
        'client_id',
        'balance',
        'total_earned',
        'total_spent',
        'expires_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BonusTransaction::class, 'client_id', 'client_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->expires_at && $this->expires_at->diffInDays(now()) <= $days;
    }

    public function updateExpiration(): void
    {
        $this->update(['expires_at' => now()->addMonths(3)]);
    }

    public function canSpend(float $amount): bool
    {
        return $this->balance >= $amount && !$this->isExpired();
    }

    public function getAvailableBalance(): float
    {
        return $this->isExpired() ? 0 : $this->balance;
    }
}
