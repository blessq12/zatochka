<?php

namespace App\Infrastructure\Finance\ReadModel;

use App\Application\Finance\DTO\CashDeskSummaryDTO;
use App\Application\Finance\DTO\CashOperationListItemDTO;
use App\Application\Finance\ReadPort\CashDeskReadPort;
use App\Domain\Finance\VO\CashOperationType;
use App\Infrastructure\Finance\Model\CashOperationModel;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Finance\Model\RefundModel;
use App\Infrastructure\Order\Model\OrderModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class EloquentCashDeskReadModel implements CashDeskReadPort
{
    public function summarize(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        string $currency = 'RUB',
        int $recentLimit = 15,
        ?string $paymentMethod = null,
    ): CashDeskSummaryDTO {
        $base = CashOperationModel::query()
            ->where('currency', $currency)
            ->whereBetween('registered_at', [$from, $to]);

        $this->applyPaymentMethodFilter($base, $paymentMethod);

        $inTotal = (clone $base)->where('type', CashOperationType::In->value)->sum('amount');
        $outTotal = (clone $base)->where('type', CashOperationType::Out->value)->sum('amount');
        $inCount = (clone $base)->where('type', CashOperationType::In->value)->count();
        $outCount = (clone $base)->where('type', CashOperationType::Out->value)->count();

        $recentQuery = CashOperationModel::query()
            ->where('currency', $currency)
            ->whereBetween('registered_at', [$from, $to])
            ->orderByDesc('registered_at')
            ->orderByDesc('id')
            ->limit($recentLimit);

        $this->applyPaymentMethodFilter($recentQuery, $paymentMethod);

        $models = $recentQuery->get();
        $orderLabels = $this->orderLabelsFor($models);

        $recent = $models
            ->map(function (CashOperationModel $model) use ($orderLabels): CashOperationListItemDTO {
                $label = $this->resolveOrderLabel($model, $orderLabels);

                return new CashOperationListItemDTO(
                    (int) $model->id,
                    (string) $model->type,
                    (string) $model->amount,
                    (string) $model->currency,
                    $model->comment !== null ? (string) $model->comment : null,
                    $model->registered_at->toIso8601String(),
                    $model->payment_id !== null ? (int) $model->payment_id : null,
                    $model->refund_id !== null ? (int) $model->refund_id : null,
                    $model->payment_method !== null ? (string) $model->payment_method : null,
                    $label['orderId'],
                    $label['orderNumber'],
                );
            })
            ->all();

        $net = (float) $inTotal - (float) $outTotal;

        return new CashDeskSummaryDTO(
            $from->format(DateTimeImmutable::ATOM),
            $to->format(DateTimeImmutable::ATOM),
            $currency,
            number_format((float) $inTotal, 2, '.', ''),
            number_format((float) $outTotal, 2, '.', ''),
            number_format($net, 2, '.', ''),
            $inCount,
            $outCount,
            $recent,
        );
    }

    /**
     * @param  Builder<CashOperationModel>  $query
     */
    private function applyPaymentMethodFilter(Builder $query, ?string $paymentMethod): void
    {
        if ($paymentMethod === null || $paymentMethod === '' || $paymentMethod === 'all') {
            return;
        }

        $query->where('payment_method', $paymentMethod);
    }

    /**
     * @param  Collection<int, CashOperationModel>  $models
     * @return array{byPaymentId: array<int, array{orderId: string, orderNumber: string}>, byRefundId: array<int, array{orderId: string, orderNumber: string}>}
     */
    private function orderLabelsFor(Collection $models): array
    {
        $paymentIds = $models->pluck('payment_id')->filter()->map(static fn ($id): int => (int) $id)->unique()->values()->all();
        $refundIds = $models->pluck('refund_id')->filter()->map(static fn ($id): int => (int) $id)->unique()->values()->all();

        $byPaymentId = [];
        if ($paymentIds !== []) {
            $payments = PaymentModel::query()
                ->whereIn('id', $paymentIds)
                ->get(['id', 'order_id']);

            $orderIds = $payments->pluck('order_id')->unique()->values()->all();
            $orders = OrderModel::query()
                ->whereIn('id', $orderIds)
                ->get(['id', 'number'])
                ->keyBy('id');

            foreach ($payments as $payment) {
                $order = $orders->get($payment->order_id);
                if ($order === null) {
                    continue;
                }
                $byPaymentId[(int) $payment->id] = [
                    'orderId' => (string) $order->id,
                    'orderNumber' => (string) $order->number,
                ];
            }
        }

        $byRefundId = [];
        if ($refundIds !== []) {
            $refunds = RefundModel::query()
                ->whereIn('id', $refundIds)
                ->get(['id', 'payment_id']);

            $refundPaymentIds = $refunds->pluck('payment_id')->unique()->values()->all();
            $payments = PaymentModel::query()
                ->whereIn('id', $refundPaymentIds)
                ->get(['id', 'order_id'])
                ->keyBy('id');

            $orderIds = $payments->pluck('order_id')->unique()->values()->all();
            $orders = OrderModel::query()
                ->whereIn('id', $orderIds)
                ->get(['id', 'number'])
                ->keyBy('id');

            foreach ($refunds as $refund) {
                $payment = $payments->get($refund->payment_id);
                if ($payment === null) {
                    continue;
                }
                $order = $orders->get($payment->order_id);
                if ($order === null) {
                    continue;
                }
                $byRefundId[(int) $refund->id] = [
                    'orderId' => (string) $order->id,
                    'orderNumber' => (string) $order->number,
                ];
            }
        }

        return ['byPaymentId' => $byPaymentId, 'byRefundId' => $byRefundId];
    }

    /**
     * @param  array{byPaymentId: array<int, array{orderId: string, orderNumber: string}>, byRefundId: array<int, array{orderId: string, orderNumber: string}>}  $labels
     * @return array{orderId: ?string, orderNumber: ?string}
     */
    private function resolveOrderLabel(CashOperationModel $model, array $labels): array
    {
        if ($model->payment_id !== null && isset($labels['byPaymentId'][(int) $model->payment_id])) {
            return $labels['byPaymentId'][(int) $model->payment_id];
        }

        if ($model->refund_id !== null && isset($labels['byRefundId'][(int) $model->refund_id])) {
            return $labels['byRefundId'][(int) $model->refund_id];
        }

        return ['orderId' => null, 'orderNumber' => null];
    }
}
