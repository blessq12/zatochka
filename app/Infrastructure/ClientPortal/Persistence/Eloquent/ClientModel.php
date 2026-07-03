<?php

namespace App\Infrastructure\ClientPortal\Persistence\Eloquent;

use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class, 'client_id')->orderByDesc('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ReviewModel::class, 'client_id')->orderByDesc('created_at');
    }
}
