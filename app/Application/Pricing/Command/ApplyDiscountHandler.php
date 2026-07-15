<?php

namespace App\Application\Pricing\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Pricing\Entity\Discount;
use App\Domain\Pricing\Repository\EstimateRepository;
use App\Domain\Pricing\VO\DiscountType;
use App\Shared\ValueObject\EntityId;

final readonly class ApplyDiscountHandler
{
    public function __construct(
        private EstimateRepository $estimates,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ApplyDiscountCommand $command): void
    {
        $estimate = $this->estimates->getById(new EntityId($command->estimateId));
        $estimate->applyDiscount(new Discount(
            new EntityId($command->discountId),
            DiscountType::from($command->type),
            $command->value,
            $command->reason,
        ));
        $this->estimates->save($estimate);
        $this->events->publish($estimate->pullDomainEvents());
    }
}
