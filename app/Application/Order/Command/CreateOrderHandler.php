<?php

namespace App\Application\Order\Command;

use App\Application\Order\Port\ClientProvisioningPort;
use App\Application\Order\ServiceType\OrderItemBuildStrategyResolver;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderUrgency;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
        private ClientProvisioningPort $clients,
        private EntityIdGenerator $ids,
        private OrderItemBuildStrategyResolver $itemBuilders,
    ) {}

    public function handle(CreateOrderCommand $command): void
    {
        if ($command->items === []) {
            throw new DomainException('Order must contain at least one item.');
        }

        $serviceType = OrderServiceType::tryFrom($command->serviceType)
            ?? throw new DomainException('Unknown order service type.');
        $billingType = OrderBillingType::tryFrom($command->billingType)
            ?? throw new DomainException('Unknown order billing type.');
        $urgency = OrderUrgency::tryFrom($command->urgency)
            ?? throw new DomainException('Unknown order urgency.');

        $warrantySourceOrderId = null;

        if ($billingType === OrderBillingType::Warranty) {
            if ($command->warrantySourceOrderId === null || $command->warrantySourceOrderId === '') {
                throw new DomainException('Warranty order requires a source order.');
            }

            $sourceOrder = $this->orders->findById(new OrderId($command->warrantySourceOrderId));

            if ($sourceOrder === null) {
                throw new DomainException('Warranty source order not found.');
            }

            $warrantySourceOrderId = $sourceOrder->id();
            $clientId = $sourceOrder->clientId()->value;

            if ($command->shouldCreateClient()) {
                throw new DomainException('Warranty order cannot create a new client.');
            }

            if ($command->clientId > 0 && $command->clientId !== $clientId) {
                throw new DomainException('Client does not match warranty source order.');
            }
        } elseif ($command->shouldCreateClient()) {
            $clientId = $this->ids->next('client')->value;
            $this->clients->register(
                $clientId,
                $this->ids->next('bonus_account')->value,
                (string) $command->newClientPhone,
                (string) $command->newClientName,
                $command->newClientEmail,
            );
        } else {
            $clientId = $command->clientId;

            if ($clientId <= 0) {
                throw new DomainException('Client is required.');
            }
        }

        $itemBuilder = $this->itemBuilders->for($serviceType);
        $items = [];

        foreach ($command->items as $itemDto) {
            $items[] = $itemBuilder->buildItem($itemDto, $clientId);
        }

        $createdAt = new \DateTimeImmutable();
        $order = Order::create(
            new OrderId($command->orderId),
            new EntityId($clientId),
            new Money($command->estimatedAmount, 'RUB'),
            $items,
            $serviceType,
            $billingType,
            $urgency,
            $command->deliveryRequired,
            $command->defects,
            $command->internalNotes,
            $warrantySourceOrderId,
            $createdAt,
            OrderNumber::fromSequenceAndDate($this->ids->next('order_number')->value, $createdAt),
        );

        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
