<?php

namespace App\Infrastructure\Documents\Read;

use App\Application\Documents\DTO\OrderDocumentSnapshot;
use App\Application\Documents\Port\OrderDocumentReadPort;
use App\Application\Inventory\DTO\OrderMaterialWriteOffLineDTO;
use App\Application\Inventory\ReadPort\OrderMaterialWriteOffReadPort;
use App\Application\Pricing\DTO\WorkPriceDTO;
use App\Application\Pricing\ReadPort\WorkPriceReadPort;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use Illuminate\Support\Collection;

final class EloquentOrderDocumentReadModel implements OrderDocumentReadPort
{
    public function __construct(
        private WorkPriceReadPort $workPrices,
        private OrderMaterialWriteOffReadPort $materialWriteOffs,
    ) {}

    public function findById(string $orderId): ?OrderDocumentSnapshot
    {
        $order = OrderModel::query()
            ->with(['client', 'items.equipment'])
            ->find($orderId);

        if ($order === null) {
            return null;
        }

        $clientName = (string) ($order->client?->name ?? '');
        $clientPhone = (string) ($order->client?->phone ?? '');
        $status = OrderStatus::tryFrom((string) $order->status);
        $serviceLabel = OrderServiceType::tryLabel((string) $order->service_type) ?? (string) $order->service_type;
        $urgency = OrderUrgency::tryFrom((string) $order->urgency);
        $urgencyLabel = $urgency?->label();
        $serviceWithUrgency = $urgency !== null && $urgency !== OrderUrgency::Normal
            ? $serviceLabel.' ('.$urgencyLabel.')'
            : $serviceLabel;

        $amount = (string) ($order->estimated_amount ?? '');
        $currency = (string) ($order->estimated_currency ?? 'RUB');
        $equipmentName = $this->equipmentName($order->items);
        $toolsList = $this->toolsList($order->items);
        $workPrices = $this->workPrices->findByOrderId($orderId);
        $materials = $this->materialWriteOffs->listActiveByOrderId($orderId);

        return new OrderDocumentSnapshot(
            orderId: (string) $order->id,
            orderNumber: (string) $order->number,
            status: (string) $order->status,
            placeholders: [
                'order_number' => (string) $order->number,
                'order_status' => $status?->label() ?? (string) $order->status,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'service_type' => $serviceLabel,
                'defects' => (string) ($order->defects ?? ''),
                'client_comment' => (string) ($order->client_comment ?? ''),
                'estimated_amount' => $amount,
                'estimated_currency' => $this->currencyLabel($currency),
                'created_at' => $order->created_at?->format('d.m.Y H:i') ?? '',
                'items_list' => $this->itemsListText($order->items),
                'order.number' => (string) $order->number,
                'order.date' => $order->created_at?->format('d.m.Y') ?? '',
                'document.date' => now()->format('d.m.Y'),
                'client.name' => $clientName,
                'client.phone' => $clientPhone,
                'service.type' => $serviceLabel,
                'service.type_with_urgency' => $serviceWithUrgency,
                'equipment.name' => $equipmentName,
                'tools.list' => $toolsList,
                'items.section' => $this->itemsSectionHtml($order->items),
                'price.section' => $this->priceSectionHtml($amount, $currency),
                'works.table' => $this->worksTableHtml($workPrices),
                'materials.table' => $this->materialsTableHtml($materials),
            ],
        );
    }

    /** @param Collection<int, OrderItemModel>|iterable<int, OrderItemModel> $items */
    private function equipmentName(iterable $items): string
    {
        $names = [];

        foreach ($items as $item) {
            $equipment = $item->equipment;
            if ($equipment === null) {
                continue;
            }

            $label = trim(implode(' ', array_filter([
                (string) ($equipment->title ?? ''),
                (string) ($equipment->brand ?? ''),
                (string) ($equipment->model_name ?? ''),
            ])));

            if ($label !== '') {
                $names[] = $label;
            }
        }

        return implode(', ', array_values(array_unique($names)));
    }

    /** @param Collection<int, OrderItemModel>|iterable<int, OrderItemModel> $items */
    private function toolsList(iterable $items): string
    {
        $tools = [];

        foreach ($items as $item) {
            if ($item->client_equipment_id !== null) {
                continue;
            }

            $name = trim((string) ($item->tool_name ?? ''));
            if ($name === '') {
                continue;
            }

            $qty = $item->quantity !== null ? ' ×'.(string) $item->quantity : '';
            $tools[] = $name.$qty;
        }

        return implode(', ', $tools);
    }

    /** @param iterable<int, OrderItemModel> $items */
    private function itemsListText(iterable $items): string
    {
        $lines = [];

        foreach ($items as $item) {
            $parts = array_filter([
                (string) ($item->tool_name ?? ''),
                $this->toolTypeLabel($item->tool_type ?? null),
                filled($item->quantity ?? null) ? '×'.(string) $item->quantity : null,
            ]);
            $line = implode(' ', $parts);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    /** @param iterable<int, OrderItemModel> $items */
    private function itemsSectionHtml(iterable $items): string
    {
        $rows = '';

        foreach ($items as $item) {
            $name = htmlspecialchars((string) ($item->tool_name ?: 'Позиция'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $type = htmlspecialchars($this->toolTypeLabel($item->tool_type ?? null) ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $qty = htmlspecialchars((string) ($item->quantity ?? '1'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $rows .= '<tr><td>'.$name.'</td><td>'.$type.'</td><td>'.$qty.'</td></tr>';
        }

        if ($rows === '') {
            return '';
        }

        return '<div class="section">'
            .'<div class="section-title">Позиции</div>'
            .'<table class="items-table"><thead><tr><th>Наименование</th><th>Тип</th><th>Кол-во</th></tr></thead><tbody>'
            .$rows
            .'</tbody></table></div>';
    }

    private function toolTypeLabel(mixed $toolType): ?string
    {
        if ($toolType === null || $toolType === '') {
            return null;
        }

        $raw = (string) $toolType;

        return SharpeningToolType::tryLabel($raw) ?? $raw;
    }

    private function priceSectionHtml(string $amount, string $currency): string
    {
        $amountLabel = $amount !== ''
            ? htmlspecialchars($amount.' '.$this->currencyLabel($currency), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            : '—';

        return '<div class="section">'
            .'<div class="section-title">Стоимость</div>'
            .'<table class="price-table"><tr><th>Ориентировочная сумма</th><td>'.$amountLabel.'</td></tr></table>'
            .'</div>';
    }

    private function currencyLabel(string $currency): string
    {
        return match (strtoupper(trim($currency))) {
            'RUB', 'RUR' => 'руб.',
            default => $currency !== '' ? $currency : 'руб.',
        };
    }

    /** @param list<WorkPriceDTO> $workPrices */
    private function worksTableHtml(array $workPrices): string
    {
        if ($workPrices === []) {
            return '';
        }

        $descriptions = PerformedWorkModel::query()
            ->whereIn('id', array_map(static fn (WorkPriceDTO $dto): int => $dto->performedWorkId, $workPrices))
            ->pluck('description', 'id');

        $rows = '';

        foreach ($workPrices as $price) {
            $title = (string) ($descriptions[$price->performedWorkId] ?? 'Работа');
            $amount = $price->finalAmount ?? $price->baseAmount;
            $rows .= '<tr><td>'.htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                .'</td><td>'.htmlspecialchars($amount.' '.$this->currencyLabel($price->currency), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                .'</td></tr>';
        }

        return '<div class="section">'
            .'<div class="section-title">Выполненные работы</div>'
            .'<table class="works-table"><thead><tr><th>Работа</th><th>Сумма</th></tr></thead><tbody>'
            .$rows
            .'</tbody></table></div>';
    }

    /** @param list<OrderMaterialWriteOffLineDTO> $materials */
    private function materialsTableHtml(array $materials): string
    {
        if ($materials === []) {
            return '';
        }

        $rows = '';

        foreach ($materials as $line) {
            $name = (string) ($line->materialName ?? 'Материал #'.$line->stockItemId);
            $rows .= '<tr><td>'.htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                .'</td><td>'.htmlspecialchars($line->quantity, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                .'</td><td>'.htmlspecialchars($line->unitPrice.' '.$this->currencyLabel($line->currency), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                .'</td></tr>';
        }

        return '<div class="section">'
            .'<div class="section-title">Материалы</div>'
            .'<table class="materials-table"><thead><tr><th>Материал</th><th>Кол-во</th><th>Цена</th></tr></thead><tbody>'
            .$rows
            .'</tbody></table></div>';
    }
}
