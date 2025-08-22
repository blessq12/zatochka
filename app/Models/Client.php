<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'full_name',
        'phone',
        'telegram',
        'birth_date',
        'delivery_address',
        'password',
        'telegram_verified_at',
        'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'telegram_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /**
     * Получить имя пользователя для аутентификации
     */
    public function getAuthIdentifierName()
    {
        return 'phone';
    }

    /**
     * Проверить, верифицирован ли Telegram
     */
    public function isTelegramVerified()
    {
        return !is_null($this->telegram_verified_at);
    }

    /**
     * Отметить Telegram как верифицированный
     */
    public function markTelegramAsVerified()
    {
        $this->update(['telegram_verified_at' => now()]);
    }

    /**
     * Получить route key name для API
     */
    public function getRouteKeyName()
    {
        return 'phone';
    }
}
