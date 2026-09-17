<?php

namespace App\Domain\Finance\Aggregate;

use App\Domain\Finance\Entity\PricingLine;
use App\Domain\Finance\OrderPricingStatus;
use App\Shared\Domain\DomainException;

final class OrderPricing
{
    /**
     * @param  list<PricingLine>  $lines
     */
    public function __construct(
        private ?int $id,
        private int $orderId,
        private OrderPricingStatus $status,
        private array $lines,
    ) {
        if ($orderId < 1) {
            throw new DomainException('order_id is required.');
        }
        $this->assertUniqueOrderItems($lines);
    }

    public static function forOrder(int $orderId): self
    {
        return new self(null, $orderId, OrderPricingStatus::Draft, []);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function status(): OrderPricingStatus
    {
        return $this->status;
    }

    /**
     * @return list<PricingLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function total(): string
    {
        $sum = 0.0;
        foreach ($this->lines as $line) {
            $sum += (float) $line->amount();
        }

        return number_format($sum, 2, '.', '');
    }

    /**
     * @param  list<array{order_item_id: int, amount: string}>  $lines
     */
    public function replaceLines(array $lines): void
    {
        $mapped = [];
        foreach ($lines as $row) {
            $mapped[] = PricingLine::create(
                (int) $row['order_item_id'],
                (string) $row['amount'],
            );
        }

        $this->assertUniqueOrderItems($mapped);
        $this->lines = $mapped;
        $this->status = $mapped === []
            ? OrderPricingStatus::Draft
            : OrderPricingStatus::Priced;
    }

    /**
     * @param  list<PricingLine>  $lines
     */
    private function assertUniqueOrderItems(array $lines): void
    {
        $seen = [];
        foreach ($lines as $line) {
            $orderItemId = $line->orderItemId();
            if (isset($seen[$orderItemId])) {
                throw new DomainException('Duplicate order_item_id in pricing lines.');
            }
            $seen[$orderItemId] = true;
        }
    }
}
