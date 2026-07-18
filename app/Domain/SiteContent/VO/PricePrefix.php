<?php

namespace App\Domain\SiteContent\VO;

use App\Shared\Domain\DomainException;

enum PricePrefix: string
{
    case From = 'from';
    case To = 'to';

    public function label(): string
    {
        return match ($this) {
            self::From => 'от',
            self::To => 'до',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function fromNullable(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        $prefix = self::tryFrom($value);

        if ($prefix === null) {
            throw new DomainException(sprintf('Unknown price prefix: %s', $value));
        }

        return $prefix;
    }
}
