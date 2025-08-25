<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Find order by number
     */
    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * Get client orders
     */
    public function getClientOrders(Client $client, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by service type
     */
    public function getByServiceType(string $serviceType, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders with delivery
     */
    public function getWithDelivery(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get recent orders
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Get orders statistics
     */
    public function getStats(): array;

    /**
     * Search orders
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;
}
