<?php

namespace App\Domain\Finance\Entity;

use App\Domain\Finance\Event\CashOperationRegistered;
use App\Domain\Finance\VO\CashOperationType;
use App\Domain\Finance\VO\PaymentMethod;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class CashOperation extends AggregateRoot
{
    private function __construct(
        private readonly EntityId $id,
        private readonly CashOperationType $type,
        private readonly Money $amount,
        private readonly DateTimeImmutable $registeredAt,
        private readonly ?string $comment = null,
        private readonly ?EntityId $paymentId = null,
        private readonly ?EntityId $refundId = null,
        private readonly ?PaymentMethod $paymentMethod = null,
    ) {
        if ((float) $this->amount->amount <= 0) {
            throw new DomainException('Cash operation amount must be positive.');
        }

        if ($this->paymentId !== null && $this->refundId !== null) {
            throw new DomainException('Cash operation cannot reference both payment and refund.');
        }
    }

    public static function register(
        EntityId $id,
        CashOperationType $type,
        Money $amount,
        ?string $comment = null,
        ?DateTimeImmutable $registeredAt = null,
        ?EntityId $paymentId = null,
        ?EntityId $refundId = null,
        ?PaymentMethod $paymentMethod = null,
    ): self {
        $operation = new self(
            $id,
            $type,
            $amount,
            $registeredAt ?? new DateTimeImmutable,
            $comment,
            $paymentId,
            $refundId,
            $paymentMethod,
        );
        $operation->record(new CashOperationRegistered($id, $amount, $type->value));

        return $operation;
    }

    public static function reconstitute(
        EntityId $id,
        CashOperationType $type,
        Money $amount,
        DateTimeImmutable $registeredAt,
        ?string $comment = null,
        ?EntityId $paymentId = null,
        ?EntityId $refundId = null,
        ?PaymentMethod $paymentMethod = null,
    ): self {
        return new self($id, $type, $amount, $registeredAt, $comment, $paymentId, $refundId, $paymentMethod);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function type(): CashOperationType
    {
        return $this->type;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function registeredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function paymentId(): ?EntityId
    {
        return $this->paymentId;
    }

    public function refundId(): ?EntityId
    {
        return $this->refundId;
    }

    public function paymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }
}
