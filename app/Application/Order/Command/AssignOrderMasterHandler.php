<?php

namespace App\Application\Order\Command;

use App\Application\Order\Port\MasterDirectoryPort;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class AssignOrderMasterHandler
{
    public function __construct(
        private OrderRepository $orders,
        private MasterDirectoryPort $masters,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(AssignOrderMasterCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            if (! $this->masters->existsAsMaster($command->masterId)) {
                throw new DomainException('Selected user is not a master.');
            }

            $order = $this->orders->getById(new OrderId($command->orderId));
            $order->assignMaster(new EntityId($command->masterId));
            $this->orders->save($order);
            $this->events->publish($order->pullDomainEvents());
        });
    }
}
