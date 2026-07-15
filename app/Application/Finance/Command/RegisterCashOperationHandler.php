<?php

namespace App\Application\Finance\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Finance\Entity\CashOperation;
use App\Domain\Finance\Repository\CashOperationRepository;
use App\Domain\Finance\VO\CashOperationType;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class RegisterCashOperationHandler
{
    public function __construct(
        private CashOperationRepository $operations,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RegisterCashOperationCommand $command): void
    {
        $operation = CashOperation::register(
            new EntityId($command->cashOperationId),
            CashOperationType::from($command->type),
            new Money($command->amount, $command->currency),
            $command->comment,
        );

        $this->operations->save($operation);
        $this->events->publish($operation->pullDomainEvents());
    }
}
