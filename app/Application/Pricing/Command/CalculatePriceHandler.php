<?php

namespace App\Application\Pricing\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Pricing\Repository\EstimateRepository;
use App\Domain\Pricing\Service\PriceCalculationService;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class CalculatePriceHandler
{
    public function __construct(
        private EstimateRepository $estimates,
        private PriceCalculationService $calculator,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CalculatePriceCommand $command): void
    {
        $estimate = $this->estimates->getById(new EntityId($command->estimateId));
        $this->calculator->bindBasePrice(
            $estimate,
            new EntityId($command->itemPriceId),
            new Money($command->baseAmount, $command->currency),
        );
        $estimate->calculatePrice();
        $this->estimates->save($estimate);
        $this->events->publish($estimate->pullDomainEvents());
    }
}
