<?php

namespace App\Domain\Pricing\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class ItemPrice
{
    private ?Discount $discount = null;
    private ?Money $finalAmount = null;

    public function __construct(
        private readonly EntityId $id,
        private readonly EntityId $orderItemId,
        private readonly Money $baseAmount,
    ) {}

    public static function reconstitute(
        EntityId $id,
        EntityId $orderItemId,
        Money $baseAmount,
        ?Discount $discount = null,
        ?Money $finalAmount = null,
    ): self {
        $price = new self($id, $orderItemId, $baseAmount);
        $price->discount = $discount;
        $price->finalAmount = $finalAmount;

        return $price;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderItemId(): EntityId
    {
        return $this->orderItemId;
    }

    public function baseAmount(): Money
    {
        return $this->baseAmount;
    }

    public function discount(): ?Discount
    {
        return $this->discount;
    }

    public function finalAmount(): ?Money
    {
        return $this->finalAmount;
    }

    public function applyDiscount(Discount $discount): void
    {
        if ($this->discount !== null) {
            throw new DomainException('Discount is already applied to this item price.');
        }

        $this->discount = $discount;
        $this->finalAmount = $discount->applyTo($this->baseAmount);
    }

    public function calculateFinal(): Money
    {
        if ($this->discount === null) {
            $this->finalAmount = $this->baseAmount;
        } elseif ($this->finalAmount === null) {
            $this->finalAmount = $this->discount->applyTo($this->baseAmount);
        }

        return $this->finalAmount;
    }
}
