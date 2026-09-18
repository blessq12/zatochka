<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Application\Order\Support\OrderItemMapper;
use App\Domain\Crm\Repository\EquipmentRepository;
use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\BillingType;
use App\Domain\Order\OrderItemKind;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Urgency;
use App\Shared\Domain\DomainException;

final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EquipmentRepository $equipments,
        private OrderResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(
        int $clientId,
        string $billingType,
        string $urgency,
        string $estimatedCost,
        bool $needsDelivery,
        ?string $deliveryAddress,
        array $items,
    ): OrderResponse {
        $billing = BillingType::tryFrom($billingType)
            ?? throw new DomainException('Invalid billing_type.');
        $urgencyEnum = Urgency::tryFrom($urgency)
            ?? throw new DomainException('Invalid urgency.');

        $mappedItems = OrderItemMapper::fromPayload($items);
        $this->assertEquipmentOwnership($clientId, $mappedItems);

        $order = Order::create(
            $clientId,
            $billing,
            $urgencyEnum,
            $estimatedCost,
            $needsDelivery,
            $deliveryAddress,
            $mappedItems,
        );

        return $this->assembler->assemble($this->orders->save($order));
    }

    /**
     * @param  list<\App\Domain\Order\Entity\OrderItem>  $items
     */
    private function assertEquipmentOwnership(int $clientId, array $items): void
    {
        foreach ($items as $item) {
            if ($item->kind() !== OrderItemKind::Repair || $item->equipmentId() === null) {
                continue;
            }

            $equipment = $this->equipments->findById((int) $item->equipmentId());
            if ($equipment === null || $equipment->isDeleted()) {
                throw new DomainException('Equipment not found.');
            }
            if ($equipment->clientId() !== $clientId) {
                throw new DomainException('Equipment does not belong to this client.');
            }
        }
    }
}
