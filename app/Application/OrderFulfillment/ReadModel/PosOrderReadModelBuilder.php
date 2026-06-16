<?php

namespace App\Application\OrderFulfillment\ReadModel;

use App\Application\OrderFulfillment\Presenter\PosOrderPresenter;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderMaterial;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;

final class PosOrderReadModelBuilder
{
    public function __construct(
        private EquipmentRepositoryInterface $equipment,
        private MasterRepositoryInterface $masters,
        private WarehouseItemRepositoryInterface $warehouseItems,
    ) {}

    /** @return array<string, mixed> */
    public function build(Order $order): array
    {
        $detail = PosOrderPresenter::detail($order);

        $detail['equipment'] = $this->equipmentBlock($order);
        $detail['master'] = $this->masterBlock($order);
        $detail['materials'] = $this->materialsBlock($order);

        return $detail;
    }

    /** @return array<string, mixed>|null */
    private function equipmentBlock(Order $order): ?array
    {
        if ($order->equipmentId() === null) {
            return null;
        }

        $equipment = $this->equipment->findById($order->equipmentId());

        if ($equipment === null) {
            return null;
        }

        return [
            'id' => $equipment->id(),
            'name' => $equipment->name(),
            'brand' => $equipment->brand(),
            'model' => $equipment->model(),
            'serial_numbers' => $equipment->serialNumbers(),
        ];
    }

    /** @return array<string, mixed>|null */
    private function masterBlock(Order $order): ?array
    {
        if ($order->masterId() === null) {
            return null;
        }

        $master = $this->masters->findById($order->masterId());

        if ($master === null) {
            return null;
        }

        return [
            'id' => $master->id(),
            'name' => $master->fullName(),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function materialsBlock(Order $order): array
    {
        return array_map(function (OrderMaterial $material): array {
            $item = $this->warehouseItems->findById($material->warehouseItemId);

            return [
                'id' => $material->id,
                'warehouse_item_id' => $material->warehouseItemId,
                'name' => $item?->name(),
                'quantity' => $material->quantity,
                'unit' => $item?->unit(),
                'unit_price' => $material->unitPrice,
                'total_price' => $material->totalPrice,
            ];
        }, $order->materials());
    }
}
