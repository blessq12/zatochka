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
use App\Filament\Support\ClientSelectField;
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

    public static function orderItemRepairableQuantity(OrderItemModel $item): int
    {
        if (! self::orderItemHasRepairableQuantity($item)) {
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
            $label = trim(($equipment->brand ?? '').' '.($equipment->model_name ?? ''));
            if ($label !== '') {
                return $label;
            }

            if (filled($equipment->title ?? null)) {
                return (string) $equipment->title;
            }
        }

        if ($item->client_equipment_id !== null) {
            return 'Оборудование #'.$item->client_equipment_id;
        }

        return 'Позиция #'.$item->id;
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

            $label = self::orderItemLabel($item);
            $quantity = $item->quantity !== null ? (int) $item->quantity : null;

            if ($quantity !== null) {
                $label .= sprintf(' — отклонено %d из %d', $rejectedQuantity, $quantity);
            }

            if ($reason !== '') {
                $label .= ': '.$reason;
            }

            $lines[] = $label;
        }

        return $lines === [] ? null : implode("\n", $lines);
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
                    ? self::orderItemLabel($item)
                    : 'Позиция #'.$movement->order_item_id;
            }

            $stockItem = StockItemModel::query()
                ->with('material')
                ->find($movement->stock_item_id);

            $materialName = $stockItem?->material?->name
                ?? 'Материал #'.$movement->stock_item_id;

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
            return 'Оборудование #'.$item->clientEquipmentId;
        }

        return 'Позиция #'.$item->id;
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
        return ClientSelectField::make($name)->required();
    }
}
