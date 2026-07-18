<?php

namespace App\Filament\Order\Resources\OrderResource\Support;

use App\Application\Order\DTO\OrderContainerItemDTO;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Domain\Inventory\VO\MovementType;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderUrgency;
use App\Filament\Inventory\Support\OrderMaterialWriteOffs;
use App\Filament\Support\ClientSelectField;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Support\View\Concerns\CanGenerateIconButtonHtml;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;

final class OrderPresentation
{
    public static function orderNumber(OrderModel $record): OrderNumber
    {
        return new OrderNumber((string) $record->number);
    }

    public static function clientListingName(OrderModel $record): string
    {
        return filled($record->client?->name) ? (string) $record->client->name : 'Без имени';
    }

    public static function clientListingPhone(OrderModel $record): string
    {
        return filled($record->client?->phone) ? (string) $record->client->phone : '—';
    }

    public static function typeFlagsHtml(OrderModel $record): HtmlString
    {
        $service = OrderServiceType::tryFrom((string) $record->service_type);
        $billing = OrderBillingType::tryFrom((string) $record->billing_type);
        $urgency = OrderUrgency::tryFrom((string) $record->urgency);

        $flags = [
            [
                'icon' => match ($service) {
                    OrderServiceType::Repair => Heroicon::OutlinedWrenchScrewdriver,
                    default => Heroicon::OutlinedScissors,
                },
                'tooltip' => $service?->label() ?? 'Тип',
                'color' => 'primary',
            ],
            [
                'icon' => match ($billing) {
                    OrderBillingType::Warranty => Heroicon::OutlinedShieldCheck,
                    default => Heroicon::OutlinedBanknotes,
                },
                'tooltip' => $billing?->label() ?? 'Вид',
                'color' => match ($billing) {
                    OrderBillingType::Warranty => 'info',
                    default => 'success',
                },
            ],
            [
                'icon' => match ($urgency) {
                    OrderUrgency::Urgent => Heroicon::OutlinedBolt,
                    default => Heroicon::OutlinedClock,
                },
                'tooltip' => $urgency?->label() ?? 'Срочность',
                'color' => match ($urgency) {
                    OrderUrgency::Urgent => 'danger',
                    default => 'gray',
                },
            ],
        ];

        $renderer = new class
        {
            use CanGenerateIconButtonHtml;

            public function render(
                Heroicon $icon,
                string $tooltip,
                string $color,
            ): string {
                return $this->generateIconButtonHtml(
                    attributes: new ComponentAttributeBag([
                        'type' => 'button',
                        'tabindex' => '-1',
                    ]),
                    color: $color,
                    hasLoadingIndicator: false,
                    icon: $icon,
                    label: $tooltip,
                    tag: 'button',
                    tooltip: $tooltip,
                    type: 'button',
                );
            }
        };

        $parts = array_map(
            static fn (array $flag): string => $renderer->render(
                $flag['icon'],
                $flag['tooltip'],
                $flag['color'],
            ),
            $flags,
        );

        return new HtmlString(
            '<div class="fi-ta-icon fi-align-center" style="display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;justify-content:center;gap:0.375rem;padding:0;width:auto;">'
            .implode('', $parts)
            .'</div>'
        );
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
     * @return list<array{
     *     position: string,
     *     material: string,
     *     quantity: string,
     *     unit_price: string,
     *     line_total: string,
     *     comment: string,
     * }>
     */
    public static function buildOrderMaterialsTableRows(OrderModel $order): array
    {
        $reversedIds = array_fill_keys(
            OrderMaterialWriteOffs::reversedWriteOffIds((string) $order->id),
            true,
        );

        $movements = WarehouseMovementModel::query()
            ->where('order_id', $order->id)
            ->where('type', MovementType::WriteOff->value)
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($movements as $movement) {
            if (isset($reversedIds[(int) $movement->id])) {
                continue;
            }

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

            $unitPrice = 'не указана';
            $lineTotal = 'не указана';

            if ($movement->unit_price !== null && $movement->unit_price !== '') {
                $currency = (string) ($movement->currency ?: 'RUB');
                $unitAmount = (float) $movement->unit_price;
                $lineAmount = round($unitAmount * (float) $movement->quantity, 2);
                $unitPrice = OrderWorkPricing::formatMoney((string) $unitAmount, $currency);
                $lineTotal = OrderWorkPricing::formatMoney((string) $lineAmount, $currency);
            }

            $rows[] = [
                'position' => $position,
                'material' => $materialName,
                'quantity' => (string) $movement->quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
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
