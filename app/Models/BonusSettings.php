<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusSettings extends Model
{
    use HasFactory;

    protected $table = 'bonus_settings';

    protected $fillable = [
        'birthday_bonus',
        'first_order_bonus',
        'rate',
        'percent_per_order',
        'min_order_sum_for_spending',
        'expire_days',
        'min_order_amount',
        'max_bonus_per_order',
    ];

    protected $casts = [
        'birthday_bonus' => 'integer',
        'first_order_bonus' => 'integer',
        'rate' => 'decimal:2',
        'percent_per_order' => 'decimal:2',
        'min_order_sum_for_spending' => 'decimal:2',
        'expire_days' => 'integer',
        'min_order_amount' => 'decimal:2',
        'max_bonus_per_order' => 'integer',
    ];

    /**
     * Получить единственную запись настроек (singleton)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'birthday_bonus' => 0,
                'first_order_bonus' => 0,
                'rate' => 1.00,
                'percent_per_order' => 5.00,
                'min_order_sum_for_spending' => 1000.00,
                'expire_days' => 365,
                'min_order_amount' => 100.00,
                'max_bonus_per_order' => 1000,
            ]
        );
    }
}
