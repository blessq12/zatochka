<?php

namespace App\Filament\Order\Resources\OrderResource\Support;

use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Domain\Inventory\VO\MovementType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Inventory\Support\OrderMaterialWriteOffs;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Infrastructure\Order\Model\OrderModel;

/**
 * Расчёт и форматирование стоимости работ по заказу для Filament-презентации.
 */
final class OrderWorkPricing
{
    public static function calculateOrderItemsTotal(OrderModel $order): string
    {
        $state = self::resolveOrderItemsTotalState($order);

        if ($state === null) {
            return 'не рассчитана';
        }

        return self::formatMoney((string) $state['total'], $state['currency']);
    }

    public static function formatOrderEstimatedTotal(OrderModel $order): string
    {
        return self::formatMoney(
            (string) $order->estimated_amount,
            (string) ($order->estimated_currency ?: 'RUB'),
        );
    }

    public static function formatOrderActualTotal(OrderModel $order): string
    {
        return self::calculateOrderItemsTotal($order);
    }

    /**
     * Листинг: ориентировочная, пока нет назначенных цен работ;
     * иначе фактическая. Для выданных — только фактическая.
     */
    public static function formatListingCost(OrderModel $order): string
    {
        if ((string) $order->status === OrderStatus::Issued->value) {
            return self::formatOrderActualTotal($order);
        }

        if (self::resolveOrderItemsTotalState($order) !== null) {
            return self::formatOrderActualTotal($order);
        }

        return self::formatOrderEstimatedTotal($order);
    }

    /**
     * @return array{
     *     works: array{total: float, currency: string}|null,
     *     parts: array{total: float, currency: string}|null,
     *     final: array{total: float, currency: string}|null,
     * }
     */
    public static function resolveOrderPricingBreakdown(OrderModel $order): array
    {
        $works = self::resolveOrderItemsTotalState($order);
        $parts = self::resolveOrderPartsTotalState($order);

        $final = null;
        if ($works !== null || $parts !== null) {
            $final = [
                'total' => (float) ($works['total'] ?? 0) + (float) ($parts['total'] ?? 0),
                'currency' => $works['currency'] ?? $parts['currency'] ?? 'RUB',
            ];
        }

        return [
            'works' => $works,
            'parts' => $parts,
            'final' => $final,
        ];
    }

    /**
     * @return array{total: float, currency: string}|null
     */
    public static function resolveOrderPartsTotalState(OrderModel $order): ?array
    {
        $reversedIds = array_fill_keys(
            OrderMaterialWriteOffs::reversedWriteOffIds((string) $order->id),
            true,
        );

        $movements = WarehouseMovementModel::query()
            ->where('order_id', $order->id)
            ->where('type', MovementType::WriteOff->value)
            ->whereNotNull('unit_price')
            ->get();

        if ($movements->isEmpty()) {
            return null;
        }

        $total = 0.0;
        $currency = (string) ($order->estimated_currency ?: 'RUB');
        $hasPricedParts = false;

        foreach ($movements as $movement) {
            if (isset($reversedIds[(int) $movement->id])) {
                continue;
            }

            if ($movement->unit_price === null || $movement->unit_price === '') {
                continue;
            }

            $hasPricedParts = true;
            $total += (float) $movement->unit_price * (float) $movement->quantity;
            $currency = (string) ($movement->currency ?: $currency);
        }

        if (! $hasPricedParts) {
            return null;
        }

        return [
            'total' => round($total, 2),
            'currency' => $currency,
        ];
    }

    /**
     * @param  array{total: float, currency: string}|null  $state
     */
    public static function formatPricingState(?array $state): string
    {
        if ($state === null) {
            return 'цены не указаны';
        }

        return self::formatMoney((string) $state['total'], $state['currency']);
    }

    /**
     * @return array{total: float, currency: string}|null
     */
    public static function resolveOrderItemsTotalState(OrderModel $order): ?array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null) {
            return null;
        }

        $total = 0.0;
        $currency = (string) ($order->estimated_currency ?: 'RUB');
        $hasPricedWorks = false;

        foreach ($container->items as $item) {
            if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                continue;
            }

            foreach ($item->works as $work) {
                $price = $work['price'] ?? null;

                if ($price === null || ! ($price['calculated'] ?? false)) {
                    continue;
                }

                $hasPricedWorks = true;
                $total += (float) $price['unit_amount'] * $item->repairableQuantity;
                $currency = (string) $price['currency'];
            }
        }

        if (! $hasPricedWorks) {
            return null;
        }

        return [
            'total' => $total,
            'currency' => $currency,
        ];
    }

    public static function formatMoney(string $amount, string $currency = 'RUB'): string
    {
        $symbol = match ($currency) {
            'RUB' => '₽',
            default => $currency,
        };

        return number_format((float) $amount, 2, '.', ' ').' '.$symbol;
    }

    /**
     * @return list<array{
     *     performed_work_id: int,
     *     order_item_id: int,
     *     position_label: string,
     *     work_description: string,
     *     repairable_quantity: int,
     *     base_amount: ?string,
     * }>
     */
    public static function buildWorkPricesFormDefaults(OrderModel $order): array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null) {
            return [];
        }

        $rows = [];

        foreach ($container->items as $item) {
            if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                continue;
            }

            foreach ($item->works as $work) {
                $positionLabel = OrderPresentation::orderContainerItemLabel($item);
                $componentName = isset($work['component_name']) && is_string($work['component_name'])
                    ? trim($work['component_name'])
                    : '';

                if ($componentName !== '') {
                    $positionLabel .= ' · '.$componentName;
                }

                $rows[] = [
                    'performed_work_id' => (int) $work['id'],
                    'order_item_id' => (int) $item->id,
                    'position_label' => $positionLabel,
                    'work_description' => (string) $work['description'],
                    'repairable_quantity' => $item->repairableQuantity,
                    'base_amount' => $work['price']['unit_amount'] ?? null,
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array{
     *     position: string,
     *     description: string,
     *     repairable_quantity: string,
     *     unit_price: string,
     *     line_total: string,
     * }>
     */
    public static function buildOrderWorkTableRows(OrderModel $order): array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null) {
            return [];
        }

        $rows = [];

        foreach ($container->items as $item) {
            if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                continue;
            }

            $position = OrderPresentation::orderContainerItemLabel($item);
            $repairableQuantity = (string) $item->repairableQuantity;

            if ($item->works === []) {
                continue;
            }

            foreach ($item->works as $index => $work) {
                $unitPrice = 'не указана';
                $lineTotal = 'не указана';
                $price = $work['price'] ?? null;

                if ($price !== null && ($price['calculated'] ?? false)) {
                    $unitAmount = (float) $price['unit_amount'];
                    $lineAmount = $unitAmount * $item->repairableQuantity;
                    $currency = (string) ($price['currency'] ?? 'RUB');
                    $unitPrice = self::formatMoney((string) $unitAmount, $currency);
                    $lineTotal = self::formatMoney((string) $lineAmount, $currency);
                }

                $workPosition = $position;
                $componentName = isset($work['component_name']) && is_string($work['component_name'])
                    ? trim($work['component_name'])
                    : '';

                if ($componentName !== '') {
                    $workPosition .= ' · '.$componentName;
                }

                $rows[] = [
                    'position' => $workPosition,
                    'description' => (string) $work['description'],
                    'repairable_quantity' => $index === 0 ? $repairableQuantity : '—',
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }
        }

        return $rows;
    }
}
