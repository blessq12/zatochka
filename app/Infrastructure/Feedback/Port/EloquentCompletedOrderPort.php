<?php


use App\Application\Feedback\Port\CompletedOrderPort;
use App\Infrastructure\Order\Model\OrderModel;

final class EloquentCompletedOrderPort implements CompletedOrderPort
{
    public function isCompletedForClient(string $orderId, int $clientId): bool
    {
        return OrderModel::query()
            ->where('id', $orderId)
            ->where('client_id', $clientId)
            ->whereIn('status', ['issued', 'closed'])
            ->exists();
    }
}
