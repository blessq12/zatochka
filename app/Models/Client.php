<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Laravel\Sanctum\HasApiTokens;

class Client extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'full_name',
        'phone',
        'telegram',
        'birth_date',
        'delivery_address',
        'password',
        'remember_token',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function bonusTransactions()
    {
        return $this->hasMany(BonusTransaction::class);
    }

    public function bonusAccount()
    {
        return $this->hasOne(BonusAccount::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function telegramChats()
    {
        return $this->hasMany(TelegramChat::class);
    }

    public function telegramMessages()
    {
        return $this->hasMany(TelegramMessage::class);
    }

    // Scope для активных клиентов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Методы для аутентификации
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
