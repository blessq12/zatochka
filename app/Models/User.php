<?php

namespace App\Models;

use App\Domain\OrderFulfillment\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'password',
        'telegram_username',
        'notifications_enabled',
        'telegram_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'telegram_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_enabled' => 'boolean',
        ];
    }

    public function masterOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'master_id');
    }

    public function fullName(): string
    {
        return trim($this->name.' '.$this->surname);
    }
}
