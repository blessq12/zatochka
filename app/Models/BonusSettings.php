<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusSettings extends Model
{
    use HasFactory;

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
     * Получить единственную запись настроек
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'birthday_bonus' => 0,
            'first_order_bonus' => 0,
            'rate' => 1.00,
            'percent_per_order' => 5.00,
            'min_order_sum_for_spending' => 1000.00,
            'expire_days' => 365,
            'min_order_amount' => 100.00,
            'max_bonus_per_order' => 1000,
        ]);
    }

    /**
     * Рассчитать бонусы за заказ
     */
    public function calculateOrderBonus(float $orderAmount): int
    {
        if ($orderAmount < $this->min_order_amount) {
            return 0;
        }

        $bonus = (int) round($orderAmount * ($this->percent_per_order / 100));
        
        return min($bonus, $this->max_bonus_per_order);
    }

    /**
     * Конвертировать бонусы в рубли
     */
    public function convertBonusToRubles(int $bonusAmount): float
    {
        return $bonusAmount * $this->rate;
    }

    /**
     * Конвертировать рубли в бонусы
     */
    public function convertRublesToBonus(float $rublesAmount): int
    {
        return (int) round($rublesAmount / $this->rate);
    }
}
