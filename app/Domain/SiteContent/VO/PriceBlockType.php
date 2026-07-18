<?php

namespace App\Domain\SiteContent\VO;

use App\Shared\Domain\DomainException;

enum PriceBlockType: string
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
        $type = self::tryFrom($value);

        if ($type === null) {
            throw new DomainException(sprintf('Unknown price block type: %s', $value));
        }

        return $type;
    }
}
