<?php

namespace App\Application\Pricing\Command;

use App\Application\Pricing\Port\OrderPricingGatePort;
use App\Application\Pricing\Port\PerformedWorkRefPort;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Pricing\Entity\WorkPrice;
use App\Domain\Pricing\Repository\WorkPriceRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class SetWorkPriceHandler
{
    public function __construct(
        private OrderPricingGatePort $orderGate,
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

        $this->orderGate->assertAwaitingPricing($work->orderId);
        $this->orderGate->assertItemPricable($work->orderId, $work->orderItemId);

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
