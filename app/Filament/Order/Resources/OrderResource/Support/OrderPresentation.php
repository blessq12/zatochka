<?php

namespace App\Filament\Order\Resources\OrderResource\Support;

use App\Application\Order\DTO\OrderContainerItemDTO;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Forms\Components\Select;

final class OrderPresentation
{
    public static function orderNumber(OrderModel $record): OrderNumber
    {
        return new OrderNumber((string) $record->number);
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            OrderStatus::Created->value => 'Создан',
            OrderStatus::MasterAssigned->value => 'Мастер назначен',
            OrderStatus::ReceptionCompleted->value => 'Приёмка завершена',
            OrderStatus::InProgress->value => 'В работе',
            OrderStatus::WorksCompleted->value => 'Работы завершены',
            OrderStatus::Ready->value => 'Готов к выдаче',
            OrderStatus::Cancelled->value => 'Отменён',
            OrderStatus::Closed->value => 'Закрыт',
            OrderStatus::Issued->value => 'Выдан',
        ];
    }

    /** @return array<string, string> */
    public static function serviceTypeOptions(): array
    {
        return [
            OrderServiceType::Sharpening->value => 'Заточка',
            OrderServiceType::Repair->value => 'Ремонт',
        ];
    }

    /** @return array<string, string> */
    public static function billingTypeOptions(): array
    {
        return [
            OrderBillingType::Paid->value => 'Платный',
            OrderBillingType::Warranty->value => 'Гарантийный',
        ];
    }

    /** @return array<string, string> */
    public static function urgencyOptions(): array
    {
        return [
            OrderUrgency::Normal->value => 'Обычный',
            OrderUrgency::Urgent->value => 'Срочный',
        ];
    }

    /** @return array<string, string> */
    public static function itemStatusOptions(): array
    {
        return [
            OrderItemStatus::Accepted->value => 'Принят',
            OrderItemStatus::InProduction->value => 'В производстве',
            OrderItemStatus::Completed->value => 'Готов',
            OrderItemStatus::Rejected->value => 'Отклонён',
            OrderItemStatus::Issued->value => 'Выдан',
        ];
    }

    public static function formatOrderItemPrice(OrderItemModel $item): string
    {
        return static::formatOrderItemWorkLineTotal($item);
    }

    /**
     * @return array{unit: float, repairable_quantity: int, currency: string}|null
     */
    public static function resolveOrderItemWorkPricing(OrderItemModel $item): ?array
    {
        if (! static::orderItemHasRepairableQuantity($item)) {
            return null;
        }

        $container = app(OrderContainerReadPort::class)->findById((string) $item->order_id);

        if ($container === null) {
            return null;
        }

        $containerItem = null;

        foreach ($container->items as $candidate) {
            if ($candidate->id === (int) $item->id) {
                $containerItem = $candidate;

                break;
            }
        }

        if ($containerItem === null || $containerItem->estimate === null || ! $containerItem->estimate['calculated']) {
            return null;
        }

        return [
            'unit' => (float) $containerItem->estimate['unit_amount'],
            'repairable_quantity' => static::orderItemRepairableQuantity($item),
            'currency' => (string) $containerItem->estimate['currency'],
        ];
    }

    public static function formatOrderItemWorkUnitPrice(OrderItemModel $item): string
    {
        $pricing = static::resolveOrderItemWorkPricing($item);

        if ($pricing === null) {
            return static::orderItemHasRepairableQuantity($item) ? 'не указана' : '—';
        }

        return static::formatMoney((string) $pricing['unit'], $pricing['currency']);
    }

    public static function formatOrderItemWorkLineTotal(OrderItemModel $item): string
    {
        $pricing = static::resolveOrderItemWorkPricing($item);

        if ($pricing === null) {
            return static::orderItemHasRepairableQuantity($item) ? 'не указана' : '—';
        }

        $lineAmount = $pricing['unit'] * $pricing['repairable_quantity'];

        return static::formatMoney((string) $lineAmount, $pricing['currency']);
    }

    public static function calculateOrderItemsTotal(OrderModel $order): string
    {
        $state = static::resolveOrderItemsTotalState($order);

        if ($state === null) {
            return 'не рассчитана';
        }

        return static::formatMoney((string) $state['total'], $state['currency']);
    }

    public static function formatOrderEstimatedTotal(OrderModel $order): string
    {
        return static::formatMoney(
            (string) $order->estimated_amount,
            (string) ($order->estimated_currency ?: 'RUB'),
        );
    }

    public static function formatOrderActualTotal(OrderModel $order): string
    {
        return static::calculateOrderItemsTotal($order);
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

        return number_format((float) $amount, 2, '.', ' ') . ' ' . $symbol;
    }

    public static function orderItemRepairableQuantity(OrderItemModel $item): int
    {
        if (! static::orderItemHasRepairableQuantity($item)) {
            return 0;
        }

        $quantity = $item->quantity !== null ? (int) $item->quantity : null;

        if ($quantity !== null) {
            return max(0, $quantity - (int) ($item->rejected_quantity ?? 0));
        }

        return 1;
    }

    public static function orderItemHasRepairableQuantity(OrderItemModel $item): bool
    {
        if ((string) $item->status === OrderItemStatus::Rejected->value) {
            return false;
        }

        $quantity = $item->quantity !== null ? (int) $item->quantity : null;
        $rejected = (int) ($item->rejected_quantity ?? 0);

        if ($quantity !== null) {
            return $rejected < $quantity;
        }

        return true;
    }

    public static function orderItemLabel(OrderItemModel $item): string
    {
        if ($item->tool_name !== null && trim((string) $item->tool_name) !== '') {
            return (string) $item->tool_name;
        }

        $equipment = $item->equipment;
        if ($equipment !== null) {
            $label = trim(($equipment->brand ?? '') . ' ' . ($equipment->model_name ?? ''));
            if ($label !== '') {
                return $label;
            }

            if (filled($equipment->title ?? null)) {
                return (string) $equipment->title;
            }
        }

        if ($item->client_equipment_id !== null) {
            return 'Оборудование #' . $item->client_equipment_id;
        }

        return 'Позиция #' . $item->id;
    }

    public static function formatOrderItemRejectionsSummary(OrderModel $order): ?string
    {
        $lines = [];

        foreach ($order->items as $item) {
            $rejectedQuantity = (int) ($item->rejected_quantity ?? 0);
            $reason = trim((string) ($item->rejection_reason ?? ''));

            if ($rejectedQuantity < 1 && (string) $item->status === OrderItemStatus::Rejected->value) {
                $rejectedQuantity = 1;
            }

            if ($rejectedQuantity < 1) {
                continue;
            }

            $label = static::orderItemLabel($item);
            $quantity = $item->quantity !== null ? (int) $item->quantity : null;

            if ($quantity !== null) {
                $label .= sprintf(' — отклонено %d из %d', $rejectedQuantity, $quantity);
            }

            if ($reason !== '') {
                $label .= ': ' . $reason;
            }

            $lines[] = $label;
        }

        return $lines === [] ? null : implode("\n", $lines);
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
                $positionLabel = static::orderContainerItemLabel($item);
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

            $position = static::orderContainerItemLabel($item);
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
                    $unitPrice = static::formatMoney((string) $unitAmount, $currency);
                    $lineTotal = static::formatMoney((string) $lineAmount, $currency);
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

    /**
     * @return list<array{position: string, material: string, quantity: string, comment: string}>
     */
    public static function buildOrderMaterialsTableRows(OrderModel $order): array
    {
        $movements = WarehouseMovementModel::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($movements as $movement) {
            $position = 'Весь заказ';

            if ($movement->order_item_id !== null) {
                $item = $order->items->firstWhere('id', $movement->order_item_id);
                $position = $item instanceof OrderItemModel
                    ? static::orderItemLabel($item)
                    : 'Позиция #' . $movement->order_item_id;
            }

            $stockItem = StockItemModel::query()
                ->with('material')
                ->find($movement->stock_item_id);

            $materialName = $stockItem?->material?->name
                ?? 'Материал #' . $movement->stock_item_id;

            $rows[] = [
                'position' => $position,
                'material' => $materialName,
                'quantity' => (string) $movement->quantity,
                'comment' => filled($movement->comment) ? (string) $movement->comment : '—',
            ];
        }

        return $rows;
    }

    public static function orderContainerItemLabel(OrderContainerItemDTO $item): string
    {
        if ($item->toolName !== null && trim($item->toolName) !== '') {
            return $item->toolName;
        }

        if ($item->clientEquipmentId !== null) {
            return 'Оборудование #' . $item->clientEquipmentId;
        }

        return 'Позиция #' . $item->id;
    }

    public static function formatMasterInternalComments(OrderModel $order): ?string
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null || $container->masterInternalComments === []) {
            return null;
        }

        return implode("\n\n", array_column($container->masterInternalComments, 'text'));
    }

    public static function clientSelect(string $name = 'client_id'): Select
    {
        return Select::make($name)
            ->label('Клиент')
            ->options(fn(): array => ClientModel::query()
                ->orderBy('name')
                ->orderBy('phone')
                ->get()
                ->mapWithKeys(static function (ClientModel $client): array {
                    $label = trim(($client->name ?: 'Без имени') . ' · ' . $client->phone);

                    return [(int) $client->id => $label];
                })
                ->all())
            ->searchable()
            ->required();
    }
}
