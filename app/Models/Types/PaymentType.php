<?php

namespace App\Models\Types;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Отношение к заказам
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Получить только активные типы платежей
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Сортировка по порядку
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Получить тип платежа по slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
