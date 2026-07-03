<?php

namespace App\Filament\Support;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Filament\Support\Icons\Heroicon;

final class OrderViewPresenter
{
    /** @var array<string, string> */
    private const SERVICE_TYPE_LABELS = [
        'sharpening' => 'Заточка',
        'repair' => 'Ремонт',
        'diagnosis' => 'Диагностика',
    ];

    /** @var array<string, string> */
    private const TOOL_TYPE_LABELS = [
        'manicure' => 'Маникюрные',
        'hair' => 'Парикмахерские',
        'grooming' => 'Грумерские',
        'groomer' => 'Грумерские',
        'barber' => 'Барберские',
        'other' => 'Другие',
    ];

    public static function clientDisplayName(OrderModel $order): string
    {
        $snapshot = $order->client_snapshot ?? [];

        return (string) ($snapshot['full_name'] ?? 'Без имени');
    }

    public static function clientPhone(OrderModel $order): ?string
    {
        $snapshot = $order->client_snapshot ?? [];

        return isset($snapshot['phone']) ? (string) $snapshot['phone'] : null;
    }

    public static function statusColor(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::New => 'gray',
            OrderStatus::InWork => 'info',
            OrderStatus::WaitingParts => 'warning',
            OrderStatus::Ready => 'success',
            OrderStatus::Issued => 'success',
            OrderStatus::Cancelled => 'danger',
        };
    }

    public static function statusIcon(OrderStatus $status): Heroicon
    {
        return match ($status) {
            OrderStatus::New => Heroicon::OutlinedInbox,
            OrderStatus::InWork => Heroicon::OutlinedWrenchScrewdriver,
            OrderStatus::WaitingParts => Heroicon::OutlinedClock,
            OrderStatus::Ready => Heroicon::OutlinedCheckCircle,
            OrderStatus::Issued => Heroicon::OutlinedArchiveBox,
            OrderStatus::Cancelled => Heroicon::OutlinedXCircle,
        };
    }

    public static function statusHint(OrderModel $order): ?string
    {
        return match (true) {
            $order->status === OrderStatus::New && $order->master_id === null => 'Назначьте мастера — после этого заказ появится в POS.',
            $order->status === OrderStatus::New && $order->master_id !== null => 'Мастер назначен. Заказ ждёт начала работы в POS.',
            $order->status === OrderStatus::InWork && filled($order->rework_feedback) => 'Заказ на доработке у мастера после вашего возврата с приёмки.',
            $order->status === OrderStatus::InWork && self::hasUnpricedWorks($order) => 'Укажите цены по типам инструментов в блоке «Состав и стоимость».',
            $order->status === OrderStatus::InWork => 'Заказ в работе. Контролируйте состав и итоговую сумму.',
            $order->status === OrderStatus::WaitingParts => 'Заказ на паузе: ожидание запчастей со склада.',
            $order->status === OrderStatus::Ready => 'Заказ готов по мнению мастера. Проверьте состав и качество — выдайте клиенту или верните на доработку.',
            $order->status === OrderStatus::Issued => 'Заказ выдан клиенту. Редактирование состава недоступно.',
            $order->status === OrderStatus::Cancelled => 'Заказ отменён и не участвует в работе мастерской.',
            default => null,
        };
    }

    public static function sourceLabel(OrderSource $source): string
    {
        return match ($source) {
            OrderSource::Manual => 'Создан вручную',
            OrderSource::SiteLead => 'Из лида с сайта',
        };
    }

    public static function urgencyLabel(OrderUrgency $urgency): string
    {
        return match ($urgency) {
            OrderUrgency::Standard => 'Стандарт',
            OrderUrgency::Urgent => 'Срочно',
        };
    }

    public static function urgencyColor(OrderUrgency $urgency): string
    {
        return match ($urgency) {
            OrderUrgency::Standard => 'gray',
            OrderUrgency::Urgent => 'danger',
        };
    }

    public static function serviceTypeLabel(string $type): string
    {
        return self::SERVICE_TYPE_LABELS[$type] ?? $type;
    }

    /**
     * @return list<string>
     */
    public static function serviceTypeLabels(mixed $types): array
    {
        $normalized = self::normalizeServiceTypes($types);

        if ($normalized === []) {
            return [];
        }

        return array_values(array_map(
            fn (string $type): string => self::SERVICE_TYPE_LABELS[$type] ?? $type,
            $normalized,
        ));
    }

    /**
     * @return list<string>
     */
    private static function normalizeServiceTypes(mixed $types): array
    {
        if ($types === null || $types === []) {
            return [];
        }

        if (is_array($types)) {
            return array_values(array_map(strval(...), $types));
        }

        if (! is_string($types)) {
            return [];
        }

        $decoded = json_decode($types, true);

        if (is_array($decoded)) {
            return array_values(array_map(strval(...), $decoded));
        }

        return $types !== '' ? [$types] : [];
    }

    public static function toolTypeLabel(string $toolType): string
    {
        return self::TOOL_TYPE_LABELS[$toolType] ?? $toolType;
    }

    public static function masterName(?int $masterId): ?string
    {
        return self::userName($masterId);
    }

    public static function managerName(?int $managerId): ?string
    {
        return self::userName($managerId);
    }

    private static function userName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        $user = UserModel::query()->find($userId);

        return $user !== null ? trim($user->name.' '.$user->surname) : null;
    }

    public static function equipmentLabel(?int $equipmentId): ?string
    {
        if ($equipmentId === null) {
            return null;
        }

        $equipment = EquipmentModel::query()->find($equipmentId);

        if ($equipment === null) {
            return null;
        }

        return trim(implode(' ', array_filter([
            $equipment->name,
            $equipment->brand,
            $equipment->model,
        ])));
    }

    public static function warehouseItemName(int $warehouseItemId): string
    {
        return WarehouseItemModel::query()->find($warehouseItemId)?->name
            ?? "Позиция #{$warehouseItemId}";
    }

    public static function worksTotal(OrderModel $order): string
    {
        $total = '0.00';

        foreach ($order->works as $work) {
            if ($work->price !== null) {
                $total = bcadd($total, (string) $work->price, 2);
            }
        }

        return $total;
    }

    public static function toolsTotal(OrderModel $order): string
    {
        $total = '0.00';

        foreach ($order->tools as $tool) {
            if ($tool->unit_price !== null) {
                $total = bcadd($total, bcmul((string) $tool->unit_price, (string) $tool->quantity, 2), 2);
            }
        }

        return $total;
    }

    public static function materialsTotal(OrderModel $order): string
    {
        $total = '0.00';

        foreach ($order->materials as $material) {
            $total = bcadd($total, (string) $material->total_price, 2);
        }

        return $total;
    }

    public static function hasUnpricedWorks(OrderModel $order): bool
    {
        if (self::isSharpeningOrder($order) && $order->tools->isNotEmpty()) {
            return self::hasUnpricedToolTypes($order);
        }

        foreach ($order->works as $work) {
            if ($work->price === null) {
                return true;
            }
        }

        return false;
    }

    public static function hasUnpricedToolTypes(OrderModel $order): bool
    {
        foreach (array_keys(self::groupedToolQuantities($order)) as $toolType) {
            $hasPrice = $order->tools->contains(
                fn ($tool): bool => $tool->tool_type === $toolType && $tool->unit_price !== null,
            );

            if (! $hasPrice) {
                return true;
            }
        }

        return false;
    }

    public static function toolTypeUnitPrice(OrderModel $order, string $toolType): ?string
    {
        $tool = $order->tools->firstWhere('tool_type', $toolType);

        if ($tool?->unit_price === null) {
            return null;
        }

        return (string) $tool->unit_price;
    }

    public static function toolTypeLineTotal(OrderModel $order, string $toolType): ?string
    {
        $total = '0.00';
        $found = false;

        foreach ($order->tools as $tool) {
            if ($tool->tool_type !== $toolType || $tool->unit_price === null) {
                continue;
            }

            $found = true;
            $total = bcadd($total, bcmul((string) $tool->unit_price, (string) $tool->quantity, 2), 2);
        }

        return $found ? $total : null;
    }

    public static function isSharpeningOrder(OrderModel $order): bool
    {
        return in_array('sharpening', $order->service_types ?? [], true);
    }

    public static function toolsTotalQuantity(OrderModel $order): int
    {
        $total = 0;

        foreach ($order->tools as $tool) {
            $total += (int) $tool->quantity;
        }

        return max($total, 1);
    }

    public static function toolsQuantityForType(OrderModel $order, string $toolType): int
    {
        $total = 0;

        foreach ($order->tools as $tool) {
            if ($tool->tool_type === $toolType) {
                $total += (int) $tool->quantity;
            }
        }

        return max($total, 1);
    }

    public static function workToolsQuantity(OrderModel $order, ?string $toolType): int
    {
        if ($toolType !== null) {
            return self::toolsQuantityForType($order, $toolType);
        }

        $types = $order->tools->pluck('tool_type')->unique()->values();

        if ($types->count() === 1) {
            return self::toolsQuantityForType($order, (string) $types->first());
        }

        return self::toolsTotalQuantity($order);
    }

    public static function groupedToolQuantities(OrderModel $order): array
    {
        $grouped = [];

        foreach ($order->tools as $tool) {
            $type = (string) $tool->tool_type;
            $grouped[$type] = ($grouped[$type] ?? 0) + (int) $tool->quantity;
        }

        return $grouped;
    }

    public static function sharpeningWorkUsesUnitPricing(OrderModel $order, ?string $toolType): bool
    {
        if (! self::isSharpeningOrder($order)) {
            return false;
        }

        if ($toolType !== null) {
            return true;
        }

        return count(self::groupedToolQuantities($order)) === 1;
    }

    public static function workUnitPrice(?string $totalPrice, int $toolsQuantity): ?string
    {
        return Order::workUnitPriceFromTotal($totalPrice, $toolsQuantity);
    }

    public static function workTotalFromUnitPrice(?string $unitPrice, int $toolsQuantity): ?string
    {
        return Order::workTotalFromUnitPrice($unitPrice, $toolsQuantity);
    }

    public static function financialSummaryLabel(OrderModel $order): string
    {
        $label = $order->price !== null
            ? 'Итого '.self::formatMoney((string) $order->price)
            : 'Итого не рассчитана';

        if (self::hasUnpricedWorks($order)) {
            $label .= ' · работы без цены';
        }

        return $label;
    }

    public static function formatMoney(?string $amount): string
    {
        if ($amount === null || bccomp($amount, '0', 2) === 0) {
            return '0 ₽';
        }

        return number_format((float) $amount, 2, '.', ' ').' ₽';
    }
}
