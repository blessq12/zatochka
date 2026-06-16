<?php

namespace App\Infrastructure\ClientPortal\Persistence\Eloquent;

use App\Domain\ClientPortal\Enum\ReviewStatus;
use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
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
