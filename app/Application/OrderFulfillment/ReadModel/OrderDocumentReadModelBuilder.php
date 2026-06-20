<?php

namespace App\Application\OrderFulfillment\ReadModel;

use App\Domain\Company\Repository\BranchRepositoryInterface;
use App\Domain\Company\Repository\CompanySettingRepositoryInterface;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderMaterial;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;

final class OrderDocumentReadModelBuilder
{
    public function __construct(
        private BranchRepositoryInterface $branches,
        private CompanySettingRepositoryInterface $companySettings,
        private EquipmentRepositoryInterface $equipment,
        private MasterRepositoryInterface $masters,
        private WarehouseItemRepositoryInterface $warehouseItems,
    ) {}

    public function build(Order $order, ?string $managerName = null): OrderDocumentData
    {
        $branch = $this->branches->findById($order->branchId());
        $settings = $this->companySettings->getValuesByKeys(['company', 'contacts']);
        $company = $settings['company'] ?? [];
        $contacts = $settings['contacts'] ?? [];

        $equipmentName = null;
        if ($order->equipmentId() !== null) {
            $equipment = $this->equipment->findById($order->equipmentId());
            if ($equipment !== null) {
                $equipmentName = trim(implode(' ', array_filter([
                    $equipment->name(),
                    $equipment->brand(),
                    $equipment->model(),
                ])));
            }
        }

        $masterName = null;
        if ($order->masterId() !== null) {
            $master = $this->masters->findById($order->masterId());
            $masterName = $master?->fullName();
        }

        $tools = array_map(
            static fn (OrderTool $tool): array => [
                'type' => $tool->toolType,
                'quantity' => $tool->quantity,
            ],
            $order->tools(),
        );

        $works = array_map(
            static fn (OrderWork $work): array => [
                'description' => $work->description,
                'price' => $work->price !== null ? (float) $work->price : 0.0,
            ],
            $order->works(),
        );

        $materials = [];
        foreach ($order->materials() as $material) {
            $materials[] = $this->materialRow($material);
        }

        $createdAt = $order->createdAt();

        return new OrderDocumentData(
            orderNumber: $order->orderNumber()->value,
            orderDate: $createdAt?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i'),
            serviceTypeLabel: $this->serviceTypeLabel($order->serviceTypes()),
            urgency: $order->urgency() === OrderUrgency::Urgent ? 'Срочный' : null,
            branchName: $branch?->name() ?? '—',
            branchAddress: $branch?->address(),
            branchPhone: $branch?->phone(),
            clientName: $order->clientDisplayName() ?? '—',
            clientPhone: $order->clientDisplayPhone() ?? '—',
            equipmentName: $equipmentName,
            tools: $tools,
            problemDescription: $order->problemDescription(),
            price: $order->price() !== null ? (float) $order->price() : null,
            managerName: $managerName,
            masterName: $masterName,
            companyName: $company['name'] ?? null,
            companyLegalName: $company['legal_name'] ?? $company['owner_name'] ?? null,
            companyInn: $company['inn'] ?? null,
            companyKpp: $company['kpp'] ?? null,
            companyOgrn: $company['ogrn'] ?? null,
            companyAddress: $this->resolveCompanyAddress($contacts, $company),
            companyPhone: $contacts['phone'] ?? null,
            works: $works,
            materials: $materials,
        );
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

    /** @param array<string, mixed> $contacts @param array<string, mixed> $company */
    private function resolveCompanyAddress(array $contacts, array $company): ?string
    {
        $address = $contacts['address'] ?? null;

        if (is_array($address)) {
            return $address['main'] ?? null;
        }

        if (is_string($address) && $address !== '') {
            return $address;
        }

        return $company['actual_address'] ?? null;
    }

    /** @return array{name: string, quantity: string, price: float} */
    private function materialRow(OrderMaterial $material): array
    {
        $item = $this->warehouseItems->findById($material->warehouseItemId);
        $name = $item?->name() ?? "Позиция #{$material->warehouseItemId}";

        return [
            'name' => $name,
            'quantity' => $material->quantity,
            'price' => (float) $material->totalPrice,
        ];
    }
}
