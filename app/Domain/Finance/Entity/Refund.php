<?php

namespace App\Domain\Finance\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class Refund
{
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        private readonly EntityId $id,
        private readonly EntityId $paymentId,
        private readonly Money $amount,
        private readonly ?string $reason = null,
        ?DateTimeImmutable $createdAt = null,
    ) {
        if ((float) $this->amount->amount <= 0) {
            throw new DomainException('Refund amount must be positive.');
        }

        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function paymentId(): EntityId
    {
        return $this->paymentId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
