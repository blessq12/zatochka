<?php

namespace App\Domain\ClientPortal\Models;

use App\Domain\OrderFulfillment\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'clients';

    protected $fillable = [
        'phone',
        'full_name',
        'email',
        'password',
        'birth_date',
        'delivery_address',
        'requires_password_set',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'requires_password_set' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'client_id');
    }
}
