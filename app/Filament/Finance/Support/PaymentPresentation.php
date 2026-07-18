<?php

namespace App\Filament\Finance\Support;

use App\Domain\Finance\VO\PaymentMethod;

final class PaymentPresentation
{
    /** @return array<string, string> */
    public static function methodOptions(): array
    {
        return PaymentMethod::options();
    }

    public static function methodLabel(?string $method): string
    {
        return PaymentMethod::tryLabel($method) ?? ($method ?: '—');
    }

    public static function formatMoney(string|float|null $amount, string $currency = 'RUB'): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        $symbol = match ($currency) {
            'RUB' => '₽',
            default => $currency,
        };

        return number_format((float) $amount, 2, '.', ' ').' '.$symbol;
    }
}
