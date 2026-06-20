<?php

namespace App\Filament\Support;

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
            $order->status === OrderStatus::New && $order->master_id === null =>
                'Назначьте мастера — после этого заказ появится в POS.',
            $order->status === OrderStatus::New && $order->master_id !== null =>
                'Мастер назначен. Заказ ждёт начала работы в POS.',
            $order->status === OrderStatus::WaitingParts =>
                'Заказ на паузе: ожидание запчастей со склада.',
            $order->status === OrderStatus::Ready =>
                'Заказ готов к выдаче. Можно распечатать акт и выдать клиенту.',
            $order->status === OrderStatus::Issued =>
                'Заказ выдан клиенту. Редактирование состава недоступно.',
            $order->status === OrderStatus::Cancelled =>
                'Заказ отменён и не участвует в работе мастерской.',
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
        if ($masterId === null) {
            return null;
        }

        $user = UserModel::query()->find($masterId);

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
}
