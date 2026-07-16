<?php

namespace App\Application\Pricing\Command;

use App\Application\Pricing\Port\PerformedWorkRefPort;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Pricing\Entity\WorkPrice;
use App\Domain\Pricing\Repository\WorkPriceRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class SetWorkPriceHandler
{
    public function __construct(
        private OrderRepository $orders,
        private WorkPriceRepository $workPrices,
        private PerformedWorkRefPort $performedWorks,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(SetWorkPriceCommand $command): void
    {
        $work = $this->performedWorks->findById($command->performedWorkId);

        if ($work === null) {
            throw new DomainException('Work record not found.');
        }

        $order = $this->orders->getById(new OrderId($work->orderId));

        if ($order->status() !== OrderStatus::WorksCompleted) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }

        $item = $order->item(new EntityId($work->orderItemId));

        if ($item->isFullyRejected()) {
            throw new DomainException('Cannot set price for a fully rejected order item.');
        }

        $money = new Money($command->baseAmount, $command->currency);
        $existing = $this->workPrices->findByPerformedWorkId(new EntityId($command->performedWorkId));

        if ($existing === null) {
            $workPrice = new WorkPrice(
                new EntityId($this->ids->next('work_price')->value),
                new EntityId($command->performedWorkId),
                new EntityId($work->orderItemId),
                $money,
            );
            $workPrice->setPrice($money);
            $this->workPrices->save($workPrice);

            return;
        }

        $existing->setPrice($money);
        $this->workPrices->save($existing);
    }
}
