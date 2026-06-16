<?php

namespace App\Application\ClientPortal\QueryHandler;

use App\Application\ClientPortal\Query\GetClientOrdersQuery;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetClientOrdersQueryHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private ReviewRepositoryInterface $reviews,
    ) {}

    /**
     * @return array{items: list<Order>, total: int, page: int, per_page: int, review_order_ids: list<int>}
     */
    public function handle(GetClientOrdersQuery $query): array
    {
        $result = $query->history
            ? $this->orders->findHistoryForClient($query->clientId, $query->page, $query->perPage)
            : $this->orders->findActiveForClient($query->clientId, $query->page, $query->perPage);

        $reviewOrderIds = [];
        foreach ($result['items'] as $order) {
            $orderId = $order->id();
            if ($orderId !== null && $this->reviews->findByOrderId($orderId) !== null) {
                $reviewOrderIds[] = $orderId;
            }
        }

        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->page,
            'per_page' => $query->perPage,
            'review_order_ids' => $reviewOrderIds,
        ];
    }
}
