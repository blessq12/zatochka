<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\ClientPortal;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ClientAuthModel extends Authenticatable
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
}
