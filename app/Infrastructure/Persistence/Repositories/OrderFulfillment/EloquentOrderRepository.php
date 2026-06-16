<?php

namespace App\Infrastructure\Persistence\Repositories\OrderFulfillment;

use App\Domain\OrderFulfillment\Entities\Order;
use App\Domain\OrderFulfillment\Entities\OrderMaterial;
use App\Domain\OrderFulfillment\Entities\OrderTool;
use App\Domain\OrderFulfillment\Entities\OrderWork;
use App\Domain\OrderFulfillment\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OrderFulfillment\OrderMaterialModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderFulfillment\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderFulfillment\OrderToolModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderFulfillment\OrderWorkModel;
use App\Infrastructure\Persistence\Mappers\OrderFulfillment\OrderMapper;
use Illuminate\Support\Facades\DB;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private OrderMapper $mapper,
    ) {}

    public function findById(int $id): ?Order
    {
        $model = OrderModel::query()
            ->with(['works', 'tools', 'materials'])
            ->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $model = $order->id() !== null
                ? OrderModel::query()->findOrFail($order->id())
                : new OrderModel;

            $this->mapper->fillModel($order, $model);
            $model->save();

            $this->syncWorks($model, $order->works());
            $this->syncTools($model, $order->tools());
            $this->syncMaterials($model, $order->materials());

            $model->load(['works', 'tools', 'materials']);

            return $this->mapper->toDomain($model);
        });
    }

    public function findLastOrderNumberForYear(int $year): ?string
    {
        return OrderModel::query()
            ->where('order_number', 'like', "ORD-{$year}-%")
            ->orderByDesc('id')
            ->value('order_number');
    }

    /** @param list<OrderWork> $works */
    private function syncWorks(OrderModel $model, array $works): void
    {
        $ids = [];

        foreach ($works as $work) {
            $workModel = $work->id !== null
                ? OrderWorkModel::query()->findOrFail($work->id)
                : new OrderWorkModel(['order_id' => $model->id]);

            $workModel->fill([
                'order_id' => $model->id,
                'description' => $work->description,
                'price' => $work->price,
                'sort_order' => $work->sortOrder,
            ]);
            $workModel->save();
            $ids[] = $workModel->id;
        }

        OrderWorkModel::query()
            ->where('order_id', $model->id)
            ->whereNotIn('id', $ids)
            ->delete();
    }

    /** @param list<OrderTool> $tools */
    private function syncTools(OrderModel $model, array $tools): void
    {
        $ids = [];

        foreach ($tools as $tool) {
            $toolModel = $tool->id !== null
                ? OrderToolModel::query()->findOrFail($tool->id)
                : new OrderToolModel(['order_id' => $model->id]);

            $toolModel->fill([
                'order_id' => $model->id,
                'tool_type' => $tool->toolType,
                'quantity' => $tool->quantity,
            ]);
            $toolModel->save();
            $ids[] = $toolModel->id;
        }

        OrderToolModel::query()
            ->where('order_id', $model->id)
            ->whereNotIn('id', $ids)
            ->delete();
    }

    /** @param list<OrderMaterial> $materials */
    private function syncMaterials(OrderModel $model, array $materials): void
    {
        $ids = [];

        foreach ($materials as $material) {
            $materialModel = $material->id !== null
                ? OrderMaterialModel::query()->findOrFail($material->id)
                : new OrderMaterialModel(['order_id' => $model->id]);

            $materialModel->fill([
                'order_id' => $model->id,
                'warehouse_item_id' => $material->warehouseItemId,
                'quantity' => $material->quantity,
                'unit_price' => $material->unitPrice,
                'total_price' => $material->totalPrice,
            ]);
            $materialModel->save();
            $ids[] = $materialModel->id;
        }

        OrderMaterialModel::query()
            ->where('order_id', $model->id)
            ->whereNotIn('id', $ids)
            ->delete();
    }
}
