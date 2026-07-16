<?php

namespace App\Application\Pricing\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Pricing\Repository\EstimateRepository;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class SetOrderItemPriceHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EstimateRepository $estimates,
        private CreateEstimateHandler $createEstimate,
        private CalculatePriceHandler $calculatePrice,
        private SequentialEntityIdGenerator $ids,
        private DomainEventPublisher $events,
    ) {}

    public function handle(SetOrderItemPriceCommand $command): void
    {
        $orderId = OrderItemModel::query()
            ->whereKey($command->orderItemId)
            ->value('order_id');

        if ($orderId === null) {
            throw new DomainException('Order item not found.');
        }

        $order = $this->orders->getById(new OrderId((string) $orderId));

        if ($order->status() !== OrderStatus::AwaitingPricing) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }

        $item = $order->item(new EntityId($command->orderItemId));

        if ($item->isFullyRejected()) {
            throw new DomainException('Cannot set price for a fully rejected order item.');
        }

        $estimate = $this->estimates->findByOrderItemId(new EntityId($command->orderItemId));

        if ($estimate === null) {
            $estimateId = $this->ids->next('estimate')->value;
            $this->createEstimate->handle(new CreateEstimateCommand(
                $estimateId,
                $command->orderItemId,
                '0.00',
                $command->currency,
            ));
            $estimate = $this->estimates->getById(new EntityId($estimateId));
        }

        if ($estimate->isCalculated()) {
            $estimate->updatePrice(new Money($command->baseAmount, $command->currency));
            $this->estimates->save($estimate);
            $this->events->publish($estimate->pullDomainEvents());

            return;
        }

        $this->calculatePrice->handle(new CalculatePriceCommand(
            $estimate->id()->value,
            $this->ids->next('item_price')->value,
            $command->baseAmount,
            $command->currency,
        ));
    }
}
