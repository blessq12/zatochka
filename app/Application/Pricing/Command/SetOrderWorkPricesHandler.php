<?php

namespace App\Application\Pricing\Command;

use App\Application\Pricing\Port\OrderPricingGatePort;
use App\Application\Shared\UnitOfWork;
use App\Shared\Domain\DomainException;

final readonly class SetOrderWorkPricesHandler
{
    public function __construct(
        private OrderPricingGatePort $orderGate,
        private SetWorkPriceHandler $setWorkPrice,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(SetOrderWorkPricesCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
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
        });
    }
}
