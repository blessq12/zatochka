<?php

namespace App\Application\Pricing\Command;

use App\Application\Pricing\Port\OrderPricingGatePort;
use App\Shared\Domain\DomainException;

final readonly class SetOrderWorkPricesHandler
{
    public function __construct(
        private OrderPricingGatePort $orderGate,
        private SetWorkPriceHandler $setWorkPrice,
    ) {}

    public function handle(SetOrderWorkPricesCommand $command): void
    {
        $this->orderGate->assertAwaitingPricing($command->orderId);

        if ($command->works === []) {
            throw new DomainException('At least one work price is required.');
        }

        foreach ($command->works as $work) {
            $this->setWorkPrice->handle(new SetWorkPriceCommand(
                (int) $work['performed_work_id'],
                (string) $work['base_amount'],
                $command->currency,
            ));
        }
    }
}
