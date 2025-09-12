<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Mapper\OrderMapper;
use App\Models\Order as EloquentOrder;

class OrderMapperImpl implements OrderMapper
{
    public function toDomain($eloquentModel): Order
    {
        if (!$eloquentModel instanceof EloquentOrder) {
            throw new \InvalidArgumentException('Expected Eloquent Order model');
        }

        return new Order(
            id: $eloquentModel->id,
            clientId: $eloquentModel->client_id,
            branchId: $eloquentModel->branch_id,
            managerId: $eloquentModel->manager_id,
            masterId: $eloquentModel->master_id,
            orderNumber: $eloquentModel->order_number,
            type: $eloquentModel->type,
            status: $eloquentModel->status,
            urgency: $eloquentModel->urgency ?? \App\Domain\Order\Enum\OrderUrgency::NORMAL,
            isPaid: (bool) $eloquentModel->is_paid,
            paidAt: $eloquentModel->paid_at ? new \DateTime($eloquentModel->paid_at) : null,
            discountId: $eloquentModel->discount_id,
            totalAmount: $eloquentModel->total_amount,
            finalPrice: $eloquentModel->final_price,
            costPrice: $eloquentModel->cost_price,
            profit: $eloquentModel->profit,
            isDeleted: (bool) $eloquentModel->is_deleted,
            createdAt: $eloquentModel->created_at ? new \DateTime($eloquentModel->created_at) : null,
            updatedAt: $eloquentModel->updated_at ? new \DateTime($eloquentModel->updated_at) : null,
        );
    }

    public function toEloquent(Order $domainEntity): array
    {
        return [
            'id' => $domainEntity->getId(),
            'client_id' => $domainEntity->getClientId(),
            'branch_id' => $domainEntity->getBranchId(),
            'manager_id' => $domainEntity->getManagerId(),
            'master_id' => $domainEntity->getMasterId(),
            'order_number' => $domainEntity->getOrderNumber(),
            'type' => $domainEntity->getType()->value,
            'status' => $domainEntity->getStatus()->value,
            'urgency' => $domainEntity->getUrgency()->value,
            'is_paid' => $domainEntity->isPaid(),
            'paid_at' => $domainEntity->getPaidAt()?->format('Y-m-d H:i:s'),
            'discount_id' => $domainEntity->getDiscountId(),
            'total_amount' => $domainEntity->getTotalAmount(),
            'final_price' => $domainEntity->getFinalPrice(),
            'cost_price' => $domainEntity->getCostPrice(),
            'profit' => $domainEntity->getProfit(),
            'is_deleted' => $domainEntity->isDeleted(),
            'created_at' => $domainEntity->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $domainEntity->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    public function fromArray(array $data): Order
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
}
