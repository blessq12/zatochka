<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\Order\Entity\ReceptionData;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Service\OrderReceptionService;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\ReceptionCondition;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class CompleteReceptionHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderReceptionService $receptionService,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(CompleteReceptionCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $order = $this->orders->getById(new OrderId($command->orderId));
            $receptionByItemId = [];

            foreach ($command->items as $itemDto) {
                $receptionByItemId[$itemDto->orderItemId] = new ReceptionData(
                    new EntityId($itemDto->receptionId),
                    new ReceptionCondition($itemDto->conditionDescription, $itemDto->visualNotes),
                    new DateTimeImmutable(),
                    $itemDto->attachmentRefs,
                );
            }

            $this->receptionService->applyReception($order, $receptionByItemId);
            $this->orders->save($order);
            $this->events->publish($order->pullDomainEvents());
        });
    }
}
