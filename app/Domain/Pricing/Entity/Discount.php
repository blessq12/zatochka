<?php

namespace App\Domain\Pricing\Entity;

use App\Domain\Pricing\VO\DiscountType;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class Discount
{
    public function __construct(
        private readonly EntityId $id,
        private readonly DiscountType $type,
        private readonly string $value,
        private readonly ?string $reason = null,
    ) {
        if (! is_numeric($this->value) || (float) $this->value < 0) {
            throw new DomainException('Discount value must be a non-negative number.');
        }

        if ($this->type === DiscountType::Percentage && (float) $this->value > 100) {
            throw new DomainException('Percentage discount cannot exceed 100.');
        }
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function type(): DiscountType
    {
        return $this->type;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function applyTo(Money $amount): Money
    {
        $base = (float) $amount->amount;

        $discounted = match ($this->type) {
            DiscountType::Percentage => $base * (1 - ((float) $this->value / 100)),
            DiscountType::Fixed => max(0, $base - (float) $this->value),
        };

        return new Money(number_format($discounted, 2, '.', ''), $amount->currency);
    }
}
