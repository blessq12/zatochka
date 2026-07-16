<?php

namespace App\Domain\Pricing\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class WorkPrice
{
    public function __construct(
        private readonly EntityId $id,
        private readonly EntityId $performedWorkId,
        private readonly EntityId $orderItemId,
        private Money $baseAmount,
        private bool $calculated = false,
        private ?Money $finalAmount = null,
    ) {}

    public static function reconstitute(
        EntityId $id,
        EntityId $performedWorkId,
        EntityId $orderItemId,
        Money $baseAmount,
        bool $calculated = false,
        ?Money $finalAmount = null,
    ): self {
        return new self($id, $performedWorkId, $orderItemId, $baseAmount, $calculated, $finalAmount);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function performedWorkId(): EntityId
    {
        return $this->performedWorkId;
    }

    public function orderItemId(): EntityId
    {
        return $this->orderItemId;
    }

    public function baseAmount(): Money
    {
        return $this->baseAmount;
    }

    public function isCalculated(): bool
    {
        return $this->calculated;
    }

    public function finalAmount(): ?Money
    {
        return $this->finalAmount;
    }

    public function setPrice(Money $baseAmount): void
    {
        if ($baseAmount->currency !== $this->baseAmount->currency) {
            throw new DomainException('Work price currency cannot be changed.');
        }

        $this->baseAmount = $baseAmount;
        $this->finalAmount = $baseAmount;
        $this->calculated = true;
    }
}
