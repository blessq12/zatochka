<?php

namespace App\Application\OrderFulfillment\ReadModel;

use App\Application\OrderFulfillment\Presenter\PosOrderPresenter;
use App\Domain\Equipment\Entity\Equipment;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use DateTimeInterface;

final class PosOrderListReadModelBuilder
{
    public function __construct(
        private EquipmentRepositoryInterface $equipment,
    ) {}

    /** @param list<Order> $orders */
    public function list(array $orders): array
    {
        return array_map($this->build(...), $orders);
    }

    /** @return array<string, mixed> */
    public function build(Order $order): array
    {
        $equipment = $this->resolveEquipment($order);
        $equipmentSummary = $this->equipmentSummary($equipment);
        $equipmentSerialNumbers = $this->equipmentSerialNumbers($equipment);
        $toolsSummary = $this->toolsSummary($order);
        $problemExcerpt = $this->excerpt($order->problemDescription(), 120);

        return [
            ...PosOrderPresenter::listItem($order),
            'is_warranty' => $order->isWarranty(),
            'service_type_label' => $this->serviceTypeLabel($order->serviceTypes()),
            'subject_line' => $this->subjectLine($order, $equipmentSummary, $toolsSummary, $problemExcerpt),
            'equipment_summary' => $equipmentSummary,
            'equipment_serial_numbers' => $equipmentSerialNumbers,
            'tools_summary' => $toolsSummary,
            'problem_excerpt' => $problemExcerpt,
            'works_count' => count($order->works()),
            'ready_at' => $order->readyAt()?->format(DateTimeInterface::ATOM),
        ];
    }

    /** @param list<string> $serviceTypes */
    private function serviceTypeLabel(array $serviceTypes): string
    {
        $labels = [];

        foreach ($serviceTypes as $type) {
            $labels[] = match ($type) {
                'sharpening' => 'Заточка',
                'repair' => 'Ремонт',
                default => $type,
            };
        }

        return $labels !== [] ? implode(', ', $labels) : '—';
    }

    private function resolveEquipment(Order $order): ?Equipment
    {
        if ($order->equipmentId() === null) {
            return null;
        }

        return $this->equipment->findById($order->equipmentId());
    }

    private function equipmentSummary(?Equipment $equipment): ?string
    {
        if ($equipment === null) {
            return null;
        }

        $brandModel = trim(implode(' ', array_filter([
            $equipment->brand(),
            $equipment->model(),
        ])));

        $name = $equipment->name();

        if ($brandModel !== '' && $name !== '') {
            return $brandModel.' — '.$name;
        }

        return $brandModel !== '' ? $brandModel : ($name !== '' ? $name : null);
    }

    /** @return array<string, string>|null */
    private function equipmentSerialNumbers(?Equipment $equipment): ?array
    {
        if ($equipment === null) {
            return null;
        }

        $serialNumbers = $equipment->serialNumbers();

        return $serialNumbers !== [] ? $serialNumbers : null;
    }

    /** @return list<array{tool_type: string, tool_type_label: string, name: string|null, quantity: int}> */
    private function toolsSummary(Order $order): array
    {
        return array_map(
            static fn (OrderTool $tool): array => [
                'tool_type' => $tool->toolType,
                'tool_type_label' => self::toolTypeLabel($tool->toolType),
                'name' => $tool->name,
                'quantity' => $tool->quantity,
            ],
            $order->tools(),
        );
    }

    /**
     * @param list<array{tool_type: string, tool_type_label: string, name: string|null, quantity: int}> $toolsSummary
     */
    private function subjectLine(
        Order $order,
        ?string $equipmentSummary,
        array $toolsSummary,
        ?string $problemExcerpt,
    ): string {
        if ($equipmentSummary !== null) {
            return $problemExcerpt !== null
                ? $equipmentSummary.' — '.$problemExcerpt
                : $equipmentSummary;
        }

        if ($toolsSummary !== []) {
            $parts = array_map(static function (array $tool): string {
                $label = $tool['name'] ?? $tool['tool_type_label'];

                return $tool['quantity'].'× '.$label;
            }, $toolsSummary);

            return implode(', ', $parts);
        }

        if ($problemExcerpt !== null) {
            return $problemExcerpt;
        }

        return $this->serviceTypeLabel($order->serviceTypes());
    }

    private static function toolTypeLabel(string $toolType): string
    {
        return match ($toolType) {
            'knife' => 'Нож',
            'scissors' => 'Ножницы',
            'clipper' => 'Машинка',
            'other' => 'Другое',
            default => $toolType,
        };
    }

    private function excerpt(?string $text, int $maxLength): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $normalized = trim($text);

        if (mb_strlen($normalized) <= $maxLength) {
            return $normalized;
        }

        return mb_substr($normalized, 0, $maxLength).'…';
    }
}
