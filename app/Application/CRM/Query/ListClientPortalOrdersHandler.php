<?php

namespace App\Application\CRM\Query;

use App\Application\CRM\DTO\ClientPortalOrderDTO;
use App\Application\Order\DTO\OrderDTO;
use App\Application\Order\ReadPort\OrderReadPort;
use App\Domain\Feedback\Repository\ReviewRepository;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;

final readonly class ListClientPortalOrdersHandler
{
    public function __construct(
        private OrderReadPort $orders,
        private ReviewRepository $reviews,
    ) {}

    /**
     * @return array{data: list<ClientPortalOrderDTO>, meta: array{total: int, page: int, per_page: int}}
     */
    public function handle(int $clientId, string $bucket, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $all = $this->orders->listByClientId($clientId);
        $filtered = array_values(array_filter(
            $all,
            fn (OrderDTO $order): bool => $bucket === 'history'
                ? $this->isHistory($order->status)
                : $this->isActive($order->status),
        ));

        $total = count($filtered);
        $slice = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        $data = [];
        foreach ($slice as $order) {
            $review = $this->reviews->findByOrderId(new OrderId($order->id));
            $reviewStatus = null;
            if ($review !== null) {
                $reviewStatus = $review->status() === ReviewStatus::PendingModeration
                    ? 'pending'
                    : $review->status()->value;
            }

            $data[] = new ClientPortalOrderDTO(
                $order->id,
                $order->number,
                [$order->serviceType],
                $order->createdAt,
                is_numeric($order->estimatedAmount) ? (float) $order->estimatedAmount : null,
                $order->defects ?? $order->internalNotes,
                $review !== null,
                $reviewStatus,
            );
        }

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ];
    }

    private function isActive(string $status): bool
    {
        return ! in_array($status, [
            OrderStatus::Issued->value,
            OrderStatus::Closed->value,
            OrderStatus::Cancelled->value,
        ], true);
    }

    private function isHistory(string $status): bool
    {
        return in_array($status, [
            OrderStatus::Issued->value,
            OrderStatus::Closed->value,
            OrderStatus::Cancelled->value,
        ], true);
    }
}
