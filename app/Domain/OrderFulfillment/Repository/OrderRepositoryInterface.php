<?php

namespace App\Domain\OrderFulfillment\Repository;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Enum\PosOrderListTab;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function save(Order $order): Order;

    public function findLastOrderNumberForYear(int $year): ?string;

    /**
     * @return array{items: list<Order>, total: int}
     */
    public function findForMaster(int $masterId, ?PosOrderListTab $tab, int $page, int $perPage): array;

    /** @return array<string, int> */
    public function countByTabForMaster(int $masterId): array;

    /**
     * @return array{items: list<Order>, total: int}
     */
    public function findActiveForClient(int $clientId, int $page, int $perPage): array;

    /**
     * @return array{items: list<Order>, total: int}
     */
    public function findHistoryForClient(int $clientId, int $page, int $perPage): array;

    public function findByIdForClient(int $orderId, int $clientId): ?Order;

    public function linkGuestOrdersByPhone(int $clientId, string $phone): int;

    /**
     * @return list<Order>
     */
    public function searchGuestOrders(string $search, int $limit = 50): array;

    /** @return list<Order> */
    public function findByEquipmentId(int $equipmentId, int $limit = 20): array;

    public function averageWorkDurationSecondsForMaster(int $masterId): ?int;
}
