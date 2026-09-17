<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Application\Order\Support\OrderItemMapper;
use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\BillingType;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Urgency;
use App\Shared\Domain\DomainException;

final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(
        int $clientId,
        string $billingType,
        string $urgency,
        string $estimatedCost,
        bool $needsDelivery,
        ?string $deliveryAddress,
        array $items,
    ): OrderResponse {
        $billing = BillingType::tryFrom($billingType)
            ?? throw new DomainException('Invalid billing_type.');
        $urgencyEnum = Urgency::tryFrom($urgency)
            ?? throw new DomainException('Invalid urgency.');

        $order = Order::create(
            $clientId,
            $billing,
            $urgencyEnum,
            $estimatedCost,
            $needsDelivery,
            $deliveryAddress,
            OrderItemMapper::fromPayload($items),
        );

        return $this->assembler->assemble($this->orders->save($order));
    }
}
