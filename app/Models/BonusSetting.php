<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BonusSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public static function getConfig(): array
    {
        return Cache::remember('bonus_config', 3600, function () {
            $setting = self::where('key', 'bonus_config')->first();

            if (!$setting) {
                $defaultConfig = [
                    'bonus_percent_per_order' => 5,
                    'bonus_exchange_rate' => 1,
                    'bonus_expiration_months' => 3,
                    'birthday_bonus_amount' => 1000,
                    'min_order_amount_for_bonus' => 1500,
                    'min_order_amount_for_spend' => 3000,
                    'max_bonus_spend_percent' => 50,
                    'first_review_bonus_amount' => 1000,
                ];

                $setting = self::create([
                    'key' => 'bonus_config',
                    'value' => $defaultConfig,
                    'description' => 'Основная конфигурация бонусной системы',
                ]);
            }

            return $setting->value ?? [];
        });
    }

    public static function updateConfig(array $config): void
    {
        self::updateOrCreate(
            ['key' => 'bonus_config'],
            [
                'value' => $config,
                'description' => 'Основная конфигурация бонусной системы',
            ]
        );

        Cache::forget('bonus_config');
    }

    public static function get(string $key, $default = null)
    {
        $config = self::getConfig();
        return $config[$key] ?? $default;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) self::get($key, $default);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return (bool) self::get($key, $default);
    }
}
