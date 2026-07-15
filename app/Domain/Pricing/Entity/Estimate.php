<?php

namespace App\Domain\Pricing\Entity;

use App\Domain\Pricing\Event\DiscountApplied;
use App\Domain\Pricing\Event\EstimateCreated;
use App\Domain\Pricing\Event\PriceCalculated;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class Estimate extends AggregateRoot
{
    private ?ItemPrice $itemPrice = null;
    private bool $calculated = false;

    private function __construct(
        private readonly EntityId $id,
        private readonly EntityId $orderItemId,
        private readonly Money $estimatedAmount,
    ) {}

    public static function create(
        EntityId $id,
        EntityId $orderItemId,
        Money $estimatedAmount,
    ): self {
        $estimate = new self($id, $orderItemId, $estimatedAmount);
        $estimate->record(new EstimateCreated($id, $orderItemId, $estimatedAmount));

        return $estimate;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderItemId(): EntityId
    {
        return $this->orderItemId;
    }

    public function estimatedAmount(): Money
    {
        return $this->estimatedAmount;
    }

    public function itemPrice(): ?ItemPrice
    {
        return $this->itemPrice;
    }

    public function attachItemPrice(ItemPrice $itemPrice): void
    {
        if (! $itemPrice->orderItemId()->equals($this->orderItemId)) {
            throw new DomainException('Item price must belong to the same order item.');
        }

        if ($this->itemPrice !== null) {
            throw new DomainException('Item price is already attached to this estimate.');
        }

        $this->itemPrice = $itemPrice;
    }

    public function applyDiscount(Discount $discount): void
    {
        if ($this->itemPrice === null) {
            throw new DomainException('Item price is required before applying a discount.');
        }

        $this->itemPrice->applyDiscount($discount);
        $this->record(new DiscountApplied($this->id, $this->itemPrice->id(), $discount->id()));
    }

    public function calculatePrice(): Money
    {
        if ($this->itemPrice === null) {
            throw new DomainException('Item price is required before calculation.');
        }

        $final = $this->itemPrice->calculateFinal();
        $this->calculated = true;
        $this->record(new PriceCalculated(
            $this->id,
            $this->orderItemId,
            $this->itemPrice->id(),
            $final,
        ));

        return $final;
    }

    public function isCalculated(): bool
    {
        return $this->calculated;
    }
}
