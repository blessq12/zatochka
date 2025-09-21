<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Client extends Model
{
    use HasApiTokens;
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'telegram',
        'telegram_verified_at',
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
        'telegram_verified_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    // Accessor для форматирования даты рождения
    public function getBirthDateAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Если это уже отформатированная дата, возвращаем как есть
        if (is_string($value) && !str_contains($value, 'T')) {
            return $value;
        }

        // Форматируем дату в Y-m-d формат
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($client) {
            $client->bonusAccount()->create([
                'balance' => 0,
            ]);
        });
    }

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
