<?php

namespace App\Domain\Order\Factory;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;

class OrderFactory
{
    public static function createFromArray(array $data): Order
    {
        return new Order(
            id: $data['id'] ?? null,
            clientId: $data['client_id'],
            branchId: $data['branch_id'],
            managerId: $data['manager_id'],
            masterId: $data['master_id'] ?? null,
            orderNumber: $data['order_number'],
            type: is_string($data['type']) ? OrderType::from($data['type']) : $data['type'],
            status: is_string($data['status']) ? OrderStatus::from($data['status']) : $data['status'],
            urgency: $data['urgency'] ?? 'normal',
            isPaid: $data['is_paid'] ?? false,
            paidAt: isset($data['paid_at']) ? new \DateTime($data['paid_at']) : null,
            discountId: $data['discount_id'] ?? null,
            totalAmount: $data['total_amount'] ?? null,
            finalPrice: $data['final_price'] ?? null,
            costPrice: $data['cost_price'] ?? null,
            profit: $data['profit'] ?? null,
            isDeleted: $data['is_deleted'] ?? false,
            createdAt: isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null,
        );
    }

    public static function createNew(
        int $clientId,
        int $branchId,
        int $managerId,
        string $orderNumber,
        OrderType $type = OrderType::REPAIR,
        OrderStatus $status = OrderStatus::NEW,
        ?int $masterId = null,
        string $urgency = 'normal'
    ): Order {
        return Order::create(
            clientId: $clientId,
            branchId: $branchId,
            managerId: $managerId,
            orderNumber: $orderNumber,
            type: $type,
            status: $status,
            masterId: $masterId,
            urgency: $urgency
        );
    }
}
