<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// ... existing code ...

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'uuid' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function orderLogs()
    {
        return $this->hasMany(OrderLog::class);
    }

    // public function inventoryTransactions()
    // {
    //     return $this->hasMany(InventoryTransaction::class);
    // }

    // Scope для активных пользователей
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Get the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the guard name for the model.
     */
    public function getGuardName(): string
    {
        return 'manager';
    }
}
