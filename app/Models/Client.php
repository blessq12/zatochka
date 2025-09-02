<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

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
}
