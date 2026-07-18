<?php

namespace App\Infrastructure\Finance\Port;

use App\Application\Finance\Port\OrderSettlementPort;
use App\Application\Finance\Port\OrderSettlementSnapshot;
use App\Domain\Order\Service\OrderItemRejectionPolicy;
use App\Domain\Order\VO\OrderItemStatus;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Pricing\Model\WorkPriceModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentOrderSettlementPort implements OrderSettlementPort
{
    public function snapshot(string $orderId): OrderSettlementSnapshot
    {
        $order = OrderModel::query()
            ->whereKey($orderId)
            ->first(['id', 'number', 'status', 'billing_type', 'estimated_currency']);

        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $currency = (string) ($order->estimated_currency ?: 'RUB');
        $total = $this->calculateWorksTotal($orderId, $currency);

        return new OrderSettlementSnapshot(
            (string) $order->id,
            (string) $order->number,
            (string) $order->status,
            (string) $order->billing_type,
            $total['amount'],
            $total['currency'],
        );
    }

    /**
     * @return array{amount: ?string, currency: string}
     */
    private function calculateWorksTotal(string $orderId, string $fallbackCurrency): array
    {
        $taskId = ProductionTaskModel::query()
            ->where('order_id', $orderId)
            ->value('id');

        if ($taskId === null) {
            return ['amount' => null, 'currency' => $fallbackCurrency];
        }

        $items = OrderItemModel::query()
            ->where('order_id', $orderId)
            ->get(['id', 'quantity', 'rejected_quantity', 'status']);

        $repairableByItem = [];

        foreach ($items as $item) {
            $quantity = $item->quantity !== null ? (int) $item->quantity : null;
            $status = (string) $item->status;
            $rejected = (int) ($item->rejected_quantity ?? 0);

            if ($status === OrderItemStatus::Rejected->value
                || OrderItemRejectionPolicy::isFullyRejected($quantity, $rejected, $status)) {
                continue;
            }

            $repairableByItem[(int) $item->id] = OrderItemRejectionPolicy::repairableQuantity(
                $quantity,
                $rejected,
                $status,
            );
        }

        if ($repairableByItem === []) {
            return ['amount' => null, 'currency' => $fallbackCurrency];
        }

        $works = PerformedWorkModel::query()
            ->where('production_task_id', $taskId)
            ->whereIn('order_item_id', array_keys($repairableByItem))
            ->get(['id', 'order_item_id']);

        if ($works->isEmpty()) {
            return ['amount' => null, 'currency' => $fallbackCurrency];
        }

        $prices = WorkPriceModel::query()
            ->whereIn('performed_work_id', $works->pluck('id'))
            ->where('calculated', true)
            ->get()
            ->keyBy('performed_work_id');

        $total = 0.0;
        $currency = $fallbackCurrency;
        $hasPriced = false;

        foreach ($works as $work) {
            $price = $prices->get($work->id);

            if ($price === null) {
                continue;
            }

            $qty = $repairableByItem[(int) $work->order_item_id] ?? 0;

            if ($qty < 1) {
                continue;
            }

            $hasPriced = true;
            $total += (float) $price->base_amount * $qty;
            $currency = (string) $price->currency;
        }

        if (! $hasPriced) {
            return ['amount' => null, 'currency' => $fallbackCurrency];
        }

        return [
            'amount' => number_format($total, 2, '.', ''),
            'currency' => $currency,
        ];
    }
}
