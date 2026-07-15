<?php

namespace App\Application\Pricing\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Pricing\Entity\Estimate;
use App\Domain\Pricing\Repository\EstimateRepository;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class CreateEstimateHandler
{
    public function __construct(
        private EstimateRepository $estimates,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CreateEstimateCommand $command): void
    {
        $estimate = Estimate::create(
            new EntityId($command->estimateId),
            new EntityId($command->orderItemId),
            new Money($command->estimatedAmount, $command->currency),
        );

        $this->estimates->save($estimate);
        $this->events->publish($estimate->pullDomainEvents());
    }
}
