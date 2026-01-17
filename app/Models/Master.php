<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Master extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'telegram_username',
        'notifications_enabled',
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
        'notifications_enabled' => 'boolean',
    ];

    // Связи
    public function orders()
    {
        return $this->hasMany(Order::class, 'master_id');
    }

    // Accessor для полного имени
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->surname, $this->name]);
        return implode(' ', $parts) ?: $this->name;
    }

    // Scope для активных мастеров
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
