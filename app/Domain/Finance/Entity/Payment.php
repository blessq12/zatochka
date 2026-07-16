<?php

namespace App\Domain\Finance\Entity;

use App\Domain\Finance\Event\PaymentAccepted;
use App\Domain\Finance\Event\RefundCreated;
use App\Domain\Finance\VO\PaymentMethod;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class Payment extends AggregateRoot
{
    /** @var list<Refund> */
    private array $refunds = [];

    private function __construct(
        private readonly EntityId $id,
        private readonly OrderId $orderId,
        private readonly Money $amount,
        private readonly PaymentMethod $method,
        private readonly DateTimeImmutable $acceptedAt,
    ) {
        if ((float) $this->amount->amount <= 0) {
            throw new DomainException('Payment amount must be positive.');
        }
    }

    public static function accept(
        EntityId $id,
        OrderId $orderId,
        Money $amount,
        PaymentMethod $method,
        ?DateTimeImmutable $acceptedAt = null,
    ): self {
        $payment = new self($id, $orderId, $amount, $method, $acceptedAt ?? new DateTimeImmutable());
        $payment->record(new PaymentAccepted($id, $orderId, $amount));

        return $payment;
    }

    /**
     * @param list<Refund> $refunds
     */
    public static function reconstitute(
        EntityId $id,
        OrderId $orderId,
        Money $amount,
        PaymentMethod $method,
        DateTimeImmutable $acceptedAt,
        array $refunds = [],
    ): self {
        $payment = new self($id, $orderId, $amount, $method, $acceptedAt);
        $payment->refunds = $refunds;

        return $payment;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function method(): PaymentMethod
    {
        return $this->method;
    }

    public function acceptedAt(): DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    /** @return list<Refund> */
    public function refunds(): array
    {
        return $this->refunds;
    }

    public function createRefund(EntityId $refundId, Money $amount, ?string $reason = null): Refund
    {
        $refunded = array_sum(array_map(
            static fn (Refund $refund): float => (float) $refund->amount()->amount,
            $this->refunds,
        ));

        if ($refunded + (float) $amount->amount > (float) $this->amount->amount) {
            throw new DomainException('Refund exceeds payment amount.');
        }

        $refund = new Refund($refundId, $this->id, $amount, $reason);
        $this->refunds[] = $refund;
        $this->record(new RefundCreated($refundId, $this->id, $amount));

        return $refund;
    }
}
