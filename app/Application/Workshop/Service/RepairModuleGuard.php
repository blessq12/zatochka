<?php

namespace App\Application\Workshop\Service;

use App\Domain\Crm\Repository\EquipmentRepository;
use App\Domain\Order\OrderItemKind;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;

final readonly class RepairModuleGuard
{
    public function __construct(
        private OrderRepository $orders,
        private EquipmentRepository $equipments,
    ) {}

    /**
     * @param  list<int>  $orderItemIds
     */
    public function assertRepairItemsHaveModules(int $orderId, array $orderItemIds): void
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $wanted = array_fill_keys(array_map('intval', $orderItemIds), true);

        foreach ($order->items() as $item) {
            if ($item->id() === null || ! isset($wanted[(int) $item->id()])) {
                continue;
            }

            if ($item->kind() !== OrderItemKind::Repair) {
                continue;
            }

            $this->assertEquipmentHasModules((int) $item->equipmentId());
        }
    }

    /**
     * @param  list<array{title: string, equipment_module_id?: int|null}>  $works
     */
    public function assertWorksMatchItem(int $orderId, int $orderItemId, array $works): void
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $item = null;
        foreach ($order->items() as $candidate) {
            if ($candidate->id() !== null && (int) $candidate->id() === $orderItemId) {
                $item = $candidate;
                break;
            }
        }

        if ($item === null) {
            throw new DomainException('Order item not found.');
        }

        if ($item->kind() === OrderItemKind::Sharpening) {
            foreach ($works as $work) {
                $moduleId = $work['equipment_module_id'] ?? null;
                if ($moduleId !== null) {
                    throw new DomainException('equipment_module_id is not allowed for sharpening works.');
                }
            }

            return;
        }

        if ($item->kind() === OrderItemKind::Repair) {
            $equipmentId = (int) $item->equipmentId();
            $moduleIds = $this->assertEquipmentHasModules($equipmentId);

            foreach ($works as $work) {
                $moduleId = $work['equipment_module_id'] ?? null;
                if ($moduleId === null || (int) $moduleId < 1) {
                    throw new DomainException('equipment_module_id is required for repair works.');
                }

                $moduleId = (int) $moduleId;
                if (! isset($moduleIds[$moduleId])) {
                    throw new DomainException('equipment_module_id does not belong to this equipment.');
                }
            }

            return;
        }

        $never = $item->kind();
        throw new DomainException('Unsupported order item kind: '.$never->value);
    }

    /**
     * @return array<int, true>
     */
    private function assertEquipmentHasModules(int $equipmentId): array
    {
        $equipment = $this->equipments->findById($equipmentId);
        if ($equipment === null) {
            throw new DomainException('Equipment not found.');
        }

        $modules = $equipment->modules();
        if ($modules === []) {
            throw new DomainException('Equipment must have at least one module before workshop works.');
        }

        $ids = [];
        foreach ($modules as $module) {
            if ($module->id() === null) {
                continue;
            }
            $ids[(int) $module->id()] = true;
        }

        return $ids;
    }
}
