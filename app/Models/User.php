<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_deleted' => 'boolean',
    ];

    // Связи

    // public function inventoryTransactions()
    // {
    //     return $this->hasMany(InventoryTransaction::class);
    // }

    // Scope для активных пользователей
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

}
