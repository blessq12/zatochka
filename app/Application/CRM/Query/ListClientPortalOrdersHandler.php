<?php

namespace App\Application\CRM\Query;

use App\Application\CRM\DTO\ClientPortalOrderDTO;
use App\Application\CRM\DTO\ClientPortalOrderItemDTO;
use App\Application\CRM\DTO\ClientPortalReviewDTO;
use App\Application\Order\DTO\OrderDTO;
use App\Application\Order\DTO\OrderItemDTO;
use App\Application\Order\ReadPort\OrderReadPort;
use App\Domain\Feedback\Entity\Review;
use App\Domain\Feedback\Repository\ReviewRepository;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\SharpeningToolType;

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
            $reviewDto = $review !== null ? $this->mapReview($review) : null;

            $data[] = new ClientPortalOrderDTO(
                $order->id,
                $order->number,
                [$order->serviceType],
                $order->status,
                $order->billingType,
                $order->urgency,
                $order->deliveryRequired,
                $order->createdAt,
                is_numeric($order->estimatedAmount) ? (float) $order->estimatedAmount : null,
                $order->clientComment,
                $order->clientComment ?? $order->defects,
                array_map(
                    fn (OrderItemDTO $item): ClientPortalOrderItemDTO => $this->mapItem($item),
                    $order->items,
                ),
                $review !== null,
                $reviewDto?->status,
                $reviewDto,
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

    private function mapItem(OrderItemDTO $item): ClientPortalOrderItemDTO
    {
        $toolTypeLabel = SharpeningToolType::tryLabel($item->toolType);
        $title = $item->toolName
            ?? $item->equipmentTitle
            ?? $toolTypeLabel
            ?? 'Позиция заказа';

        return new ClientPortalOrderItemDTO(
            $item->id,
            $title,
            $toolTypeLabel,
            $item->quantity,
            $item->status,
            OrderItemStatus::tryLabel($item->status) ?? 'Статус неизвестен',
        );
    }

    private function mapReview(Review $review): ClientPortalReviewDTO
    {
        $status = $review->status() === ReviewStatus::PendingModeration
            ? 'pending'
            : $review->status()->value;

        return new ClientPortalReviewDTO(
            $review->rating()->value,
            $review->comment(),
            $review->managerReply(),
            $status,
            $review->submittedAt()->format(\DateTimeInterface::ATOM),
        );
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
