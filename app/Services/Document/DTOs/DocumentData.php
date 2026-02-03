<?php

namespace App\Services\Document\DTOs;

class DocumentData
{
    public function __construct(
        public readonly string $orderNumber,
        public readonly string $orderDate,
        public readonly string $clientName,
        public readonly string $clientPhone,
        public readonly ?string $clientEmail,
        public readonly string $serviceType,
        public readonly string $serviceTypeLabel,
        public readonly ?string $equipmentName,
        public readonly ?string $problemDescription,
        public readonly array $tools,
        public readonly ?float $price,
        public readonly string $branchName,
        public readonly ?string $branchAddress,
        public readonly ?string $branchPhone,
        public readonly ?string $masterName,
        public readonly ?string $managerName,
        public readonly array $works,
        public readonly array $materials,
        public readonly string $urgency,
        public readonly bool $needsDelivery,
        public readonly ?string $deliveryAddress,
        public readonly ?string $companyName,
        public readonly ?string $companyLegalName,
        public readonly ?string $companyInn,
        public readonly ?string $companyKpp,
        public readonly ?string $companyOgrn,
        public readonly ?string $companyPhone,
        public readonly ?string $companyAddress,
    ) {}

    public static function fromOrder(\App\Models\Order $order): self
    {
        $order->loadMissing([
            'client',
            'branch',
            'branch.company',
            'master',
            'manager',
            'equipment',
            'tools',
            'orderWorks',
            'orderMaterials',
        ]);

        $tools = $order->tools->map(function ($tool) {
            return [
                'type' => $tool->tool_type_label ?: $tool->tool_type,
                'quantity' => $tool->quantity,
                'description' => $tool->description,
            ];
        })->toArray();

        $works = $order->orderWorks->map(function ($work) {
            return [
                'name' => $work->description ?? '',
                'price' => (float) ($work->work_price ?? 0),
                'comment' => $work->description ?? '',
            ];
        })->toArray();

        // Материалы привязаны к заказу (без дубликатов по имени)
        $materialsMap = [];
        foreach ($order->orderMaterials as $material) {
            $key = $material->name;
            $total = (float) ($material->quantity * ($material->price ?? 0));
            if (isset($materialsMap[$key])) {
                $materialsMap[$key]['quantity'] += $material->quantity;
                $materialsMap[$key]['price'] += $total;
            } else {
                $materialsMap[$key] = [
                    'name' => $material->name,
                    'quantity' => $material->quantity,
                    'price' => $total,
                ];
            }
        }
        $materials = array_values($materialsMap);

        return new self(
            orderNumber: $order->order_number,
            orderDate: $order->created_at->format('d.m.Y H:i'),
            clientName: $order->client->full_name ?? 'Не указано',
            clientPhone: $order->client->phone ?? 'Не указано',
            clientEmail: $order->client->email,
            serviceType: $order->service_type,
            serviceTypeLabel: \App\Models\Order::getAvailableTypes()[$order->service_type] ?? $order->service_type,
            equipmentName: $order->equipment?->name,
            problemDescription: $order->problem_description,
            tools: $tools,
            price: $order->price,
            branchName: $order->branch->name ?? 'Не указано',
            branchAddress: $order->branch->address,
            branchPhone: $order->branch->phone,
            masterName: $order->master?->full_name,
            managerName: $order->manager?->name,
            works: $works,
            materials: $materials,
            urgency: \App\Models\Order::getAvailableUrgencies()[$order->urgency] ?? $order->urgency,
            needsDelivery: $order->needs_delivery,
            deliveryAddress: $order->delivery_address,
            companyName: $order->branch?->company?->name ?? null,
            companyLegalName: $order->branch?->company?->legal_name ?? null,
            companyInn: $order->branch?->company?->inn ?? null,
            companyKpp: $order->branch?->company?->kpp ?? null,
            companyOgrn: $order->branch?->company?->ogrn ?? null,
            companyPhone: $order->branch?->phone ?? null,
            companyAddress: $order->branch?->company?->legal_address ?? $order->branch?->address ?? null,
        );
    }
}
