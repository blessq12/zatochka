<?php

namespace App\Application\Order\Command;

use App\Application\Pricing\ReadPort\WorkPriceReadPort;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Infrastructure\Workshop\Model\MasterCommentModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Shared\Domain\DomainException;

final readonly class MarkOrderReadyHandler
{
    public function __construct(
        private OrderRepository $orders,
        private WorkPriceReadPort $workPrices,
        private DomainEventPublisher $events,
    ) {}

    public function handle(MarkOrderReadyCommand $command): void
    {
        $order = $this->orders->getById(new OrderId($command->orderId));
        $taskId = ProductionTaskModel::query()
            ->where('order_id', $command->orderId)
            ->value('id');

        if ($taskId === null) {
            throw new DomainException('Production task not found for order.');
        }

        $workComments = MasterCommentModel::query()
            ->where('production_task_id', $taskId)
            ->whereNotNull('order_item_id')
            ->get();

        foreach ($order->items() as $item) {
            if ($item->isFullyRejected()) {
                continue;
            }

            $itemWorks = $workComments->filter(
                static fn ($comment) => (int) $comment->order_item_id === $item->id()->value,
            );

            if ($itemWorks->isEmpty()) {
                throw new DomainException(sprintf(
                    'Item #%d has no completed works to price.',
                    $item->id()->value,
                ));
            }

            foreach ($itemWorks as $workComment) {
                $workPrice = $this->workPrices->findByMasterCommentId((int) $workComment->id);

                if ($workPrice === null || ! $workPrice->calculated) {
                    throw new DomainException(sprintf(
                        'Work #%d does not have a calculated price yet.',
                        $workComment->id,
                    ));
                }
            }
        }

        $order->markReady();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
