<?php

namespace App\Domain\Warehouse\Enum;

enum StockMovementType: string
{
    case RECEIPT = 'receipt';
    case ISSUE = 'issue';
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';
    case RETURN = 'return';
    case DAMAGE = 'damage';
    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::RECEIPT => 'Поступление',
            self::ISSUE => 'Выдача',
            self::ADJUSTMENT_IN => 'Корректировка (+)',
            self::ADJUSTMENT_OUT => 'Корректировка (-)',
            self::RETURN => 'Возврат',
            self::DAMAGE => 'Списание (повреждение)',
            self::EXPIRED => 'Списание (истечение)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::RECEIPT, self::RETURN, self::ADJUSTMENT_IN => 'success',
            self::ISSUE, self::ADJUSTMENT_OUT, self::DAMAGE, self::EXPIRED => 'danger',
        };
    }

    public function isInbound(): bool
    {
        return match ($this) {
            self::RECEIPT, self::ADJUSTMENT_IN, self::RETURN => true,
            default => false,
        };
    }

    public function isOutbound(): bool
    {
        return match ($this) {
            self::ISSUE, self::ADJUSTMENT_OUT, self::DAMAGE, self::EXPIRED => true,
            default => false,
        };
    }

    public function getQuantityMultiplier(): int
    {
        return $this->isInbound() ? 1 : -1;
    }

    public static function getInboundTypes(): array
    {
        return array_filter(self::cases(), fn($type) => $type->isInbound());
    }

    public static function getOutboundTypes(): array
    {
        return array_filter(self::cases(), fn($type) => $type->isOutbound());
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
