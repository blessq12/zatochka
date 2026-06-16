<?php

namespace App\Application\Equipment\QueryHandler;

use App\Application\Equipment\Query\GetEquipmentOrderHistoryQuery;
use App\Domain\Equipment\Exception\EquipmentNotFoundException;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetEquipmentOrderHistoryQueryHandler
{
    public function __construct(
        private EquipmentRepositoryInterface $equipment,
        private OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return list<\App\Domain\OrderFulfillment\Entity\Order>
     */
    public function handle(GetEquipmentOrderHistoryQuery $query): array
    {
        if ($this->equipment->findById($query->equipmentId) === null) {
            throw EquipmentNotFoundException::withId($query->equipmentId);
        }

        return $this->orders->findByEquipmentId($query->equipmentId, $query->limit);
    }
}
