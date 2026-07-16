<?php

namespace App\Application\Pricing\Command;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Pricing\Entity\WorkPrice;
use App\Domain\Pricing\Repository\WorkPriceRepository;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Infrastructure\Workshop\Model\MasterCommentModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class SetWorkPriceHandler
{
    public function __construct(
        private OrderRepository $orders,
        private WorkPriceRepository $workPrices,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function handle(SetWorkPriceCommand $command): void
    {
        $comment = MasterCommentModel::query()->find($command->masterCommentId);

        if ($comment === null) {
            throw new DomainException('Work record not found.');
        }

        if ($comment->order_item_id === null) {
            throw new DomainException('Only completed works can be priced.');
        }

        $orderItemId = (int) $comment->order_item_id;
        $orderId = OrderItemModel::query()->whereKey($orderItemId)->value('order_id');

        if ($orderId === null) {
            throw new DomainException('Order item for work not found.');
        }

        $task = ProductionTaskModel::query()->find($comment->production_task_id);

        if ($task === null || (string) $task->order_id !== (string) $orderId) {
            throw new DomainException('Work does not belong to this order.');
        }

        $order = $this->orders->getById(new OrderId((string) $orderId));

        if ($order->status() !== OrderStatus::AwaitingPricing) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }

        $item = $order->item(new EntityId($orderItemId));

        if ($item->isFullyRejected()) {
            throw new DomainException('Cannot set price for a fully rejected order item.');
        }

        $money = new Money($command->baseAmount, $command->currency);
        $existing = $this->workPrices->findByMasterCommentId(new EntityId($command->masterCommentId));

        if ($existing === null) {
            $workPrice = new WorkPrice(
                new EntityId($this->ids->next('work_price')->value),
                new EntityId($command->masterCommentId),
                new EntityId($orderItemId),
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
