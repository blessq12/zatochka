<?php

namespace App\Domain\SiteContent\VO;

use App\Shared\Domain\DomainException;

enum ServicePriceCategory: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';

    public function label(): string
    {
        return match ($this) {
            self::Sharpening => 'Заточка',
            self::Repair => 'Ремонт',
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

    public static function fromString(string $value): self
    {
        $category = self::tryFrom($value);

        if ($category === null) {
            throw new DomainException(sprintf('Unknown service price category: %s', $value));
        }

        return $category;
    }
}
