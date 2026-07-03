<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Repository;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderMaterial;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\PosOrderListTab;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderMaterialModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderToolModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderWorkModel;
use App\Infrastructure\OrderFulfillment\Persistence\Mapper\OrderMapper;
use Illuminate\Database\Eloquent\Builder;
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

    /** @return array{items: list<Order>, total: int} */
    public function findForMaster(int $masterId, ?PosOrderListTab $tab, int $page, int $perPage): array
    {
        $query = OrderModel::query()
            ->with(['works', 'tools', 'materials'])
            ->where('master_id', $masterId);

        if ($tab !== null) {
            $query->where('status', $tab->orderStatus());
        }

        if ($tab === PosOrderListTab::Completed) {
            $query->orderByDesc('ready_at');
        } else {
            $query
                ->orderByRaw("CASE WHEN urgency = 'urgent' THEN 0 ELSE 1 END")
                ->orderByDesc('created_at');
        }

        $total = (clone $query)->count();
        $models = $query->forPage($page, $perPage)->get();

        return [
            'items' => $models->map(fn (OrderModel $model) => $this->mapper->toDomain($model))->all(),
            'total' => $total,
        ];
    }

    public function countByTabForMaster(int $masterId): array
    {
        $raw = OrderModel::query()
            ->where('master_id', $masterId)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'new' => (int) ($raw[OrderStatus::New->value] ?? 0),
            'active' => (int) ($raw[OrderStatus::InWork->value] ?? 0),
            'waiting_parts' => (int) ($raw[OrderStatus::WaitingParts->value] ?? 0),
            'completed' => (int) ($raw[OrderStatus::Ready->value] ?? 0),
        ];
    }

    public function findActiveForClient(int $clientId, int $page, int $perPage): array
    {
        $query = OrderModel::query()
            ->with(['works', 'tools', 'materials'])
            ->where('client_id', $clientId)
            ->whereNotIn('status', [OrderStatus::Issued, OrderStatus::Cancelled]);

        return $this->paginateOrders($query, $page, $perPage);
    }

    public function findHistoryForClient(int $clientId, int $page, int $perPage): array
    {
        $query = OrderModel::query()
            ->with(['works', 'tools', 'materials'])
            ->where('client_id', $clientId)
            ->whereIn('status', [OrderStatus::Issued, OrderStatus::Cancelled]);

        return $this->paginateOrders($query, $page, $perPage);
    }

    public function findByIdForClient(int $orderId, int $clientId): ?Order
    {
        $model = OrderModel::query()
            ->with(['works', 'tools', 'materials'])
            ->where('id', $orderId)
            ->where('client_id', $clientId)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function linkGuestOrdersByPhone(int $clientId, string $phone): int
    {
        return OrderModel::query()
            ->whereNull('client_id')
            ->where('client_snapshot->phone', $phone)
            ->update(['client_id' => $clientId]);
    }

    public function searchGuestOrders(string $search, int $limit = 50): array
    {
        $query = OrderModel::query()
            ->whereNull('client_id')
            ->orderByDesc('created_at')
            ->limit($limit);

        $search = trim($search);
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhere('client_snapshot->full_name', 'like', "%{$search}%")
                    ->orWhere('client_snapshot->phone', 'like', "%{$search}%");
            });
        }

        return $query
            ->get()
            ->map(fn (OrderModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function findByEquipmentId(int $equipmentId, int $limit = 20): array
    {
        return OrderModel::query()
            ->with(['works', 'tools', 'materials'])
            ->where('equipment_id', $equipmentId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (OrderModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function averageWorkDurationSecondsForMaster(int $masterId): ?int
    {
        $models = OrderModel::query()
            ->where('master_id', $masterId)
            ->whereNotNull('taken_at')
            ->get(['taken_at', 'ready_at']);

        if ($models->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;

        foreach ($models as $model) {
            $end = $model->ready_at ?? now();
            $totalSeconds += $model->taken_at->diffInSeconds($end);
        }

        return (int) round($totalSeconds / $models->count());
    }

    /**
     * @return array{items: list<Order>, total: int}
     */
    private function paginateOrders(Builder $query, int $page, int $perPage): array
    {
        $query->orderByDesc('created_at');

        $total = (clone $query)->count();
        $models = $query->forPage($page, $perPage)->get();

        return [
            'items' => $models->map(fn (OrderModel $model) => $this->mapper->toDomain($model))->all(),
            'total' => $total,
        ];
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
                'tool_type' => $work->toolType,
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
                'name' => $tool->name,
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
