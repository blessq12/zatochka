<?php

namespace App\Application\Order\ServiceType;

use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\VO\SharpeningToolType;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class SharpeningOrderItemBuildStrategy implements OrderItemBuildStrategy
{
    public function __construct(
        private EntityIdGenerator $ids,
    ) {}

    public function buildItem(CreateOrderItemDTO $itemDto): OrderItem
    {
        if (! filled($itemDto->toolName)) {
            throw new DomainException('Sharpening item requires a tool name.');
        }

        $toolType = SharpeningToolType::tryFrom((string) $itemDto->toolType)
            ?? throw new DomainException('Unknown sharpening tool type.');

        $orderItemId = $itemDto->orderItemId ?? $this->ids->next('order_item')->value;

        return OrderItem::forTool(
            new EntityId($orderItemId),
            (string) $itemDto->toolName,
            $toolType,
            (int) ($itemDto->quantity ?? 0),
        );
    }
}
