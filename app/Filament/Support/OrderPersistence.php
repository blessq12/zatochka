<?php

namespace App\Filament\Support;

use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Infrastructure\Company\Persistence\Eloquent\BranchModel;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderMaterialModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderWorkModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class OrderPersistence
{
    public static function generateOrderNumber(): string
    {
        $year = (int) date('Y');
        $lastNumber = OrderModel::query()
            ->where('order_number', 'like', "ORD-{$year}-%")
            ->orderByDesc('id')
            ->value('order_number');

        $sequence = 1;

        if ($lastNumber !== null && preg_match('/ORD-\d{4}-(\d+)/', $lastNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('ORD-%s-%04d', $year, $sequence);
    }

    public static function firstActiveBranchId(): int
    {
        $branchId = BranchModel::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        if ($branchId === null) {
            throw new RuntimeException('Активный филиал не найден.');
        }

        return (int) $branchId;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function createFromFormData(array $data, ?SiteLeadModel $lead = null): OrderModel
    {
        $clientId = $data['client_id'] ?? null;
        $clientSnapshot = null;

        if ($clientId === null) {
            $clientSnapshot = [
                'full_name' => $data['client_full_name'] ?? $data['full_name'] ?? '',
                'phone' => $data['client_phone'] ?? $data['phone'] ?? '',
            ];
        }

        $payload = [
            'order_number' => self::generateOrderNumber(),
            'status' => OrderStatus::New,
            'service_types' => $data['service_types'] ?? [],
            'urgency' => isset($data['urgency'])
                ? OrderUrgency::from($data['urgency'])
                : OrderUrgency::Standard,
            'needs_delivery' => (bool) ($data['needs_delivery'] ?? false),
            'delivery_address' => $data['delivery_address'] ?? null,
            'problem_description' => $data['problem_description'] ?? $data['comment'] ?? null,
            'source' => $lead !== null ? OrderSource::SiteLead : OrderSource::Manual,
            'client_id' => $clientId,
            'client_snapshot' => $clientSnapshot,
            'lead_id' => $lead?->id,
            'branch_id' => self::firstActiveBranchId(),
        ];

        if ($lead !== null) {
            return DB::transaction(function () use ($payload, $lead): OrderModel {
                if ($lead->converted) {
                    throw new RuntimeException('Заявка уже конвертирована в заказ.');
                }

                $order = OrderModel::query()->create($payload);

                $lead->update([
                    'converted' => true,
                    'order_id' => $order->id,
                ]);

                return $order;
            });
        }

        return OrderModel::query()->create($payload);
    }

    public static function assignMaster(OrderModel $order, int $masterId): void
    {
        $order->update(['master_id' => $masterId]);
    }

    public static function issue(OrderModel $order): void
    {
        $order->update([
            'status' => OrderStatus::Issued,
            'issued_at' => now(),
        ]);
    }

    public static function cancel(OrderModel $order): void
    {
        $order->update(['status' => OrderStatus::Cancelled]);
    }

    public static function linkEquipment(OrderModel $order, int $equipmentId): void
    {
        $order->update(['equipment_id' => $equipmentId]);
    }

    /**
     * @param  array<int, string|null>  $pricesBySortOrder
     */
    public static function setWorkPrices(OrderModel $order, array $pricesBySortOrder): void
    {
        foreach ($pricesBySortOrder as $sortOrder => $price) {
            OrderWorkModel::query()
                ->where('order_id', $order->id)
                ->where('sort_order', (int) $sortOrder)
                ->update([
                    'price' => $price !== null && $price !== ''
                        ? number_format((float) $price, 2, '.', '')
                        : null,
                ]);
        }
    }

    public static function recalculatePrice(OrderModel $order): ?string
    {
        $order->load(['works', 'materials']);

        $total = '0.00';

        foreach ($order->works as $work) {
            if ($work->price !== null) {
                $total = bcadd($total, (string) $work->price, 2);
            }
        }

        foreach ($order->materials as $material) {
            $total = bcadd($total, (string) $material->total_price, 2);
        }

        $price = bccomp($total, '0', 2) === 0 ? null : $total;

        $order->update(['price' => $price]);

        return $price;
    }

    public static function addMaterial(OrderModel $order, int $warehouseItemId, string $quantity): void
    {
        app(AddMaterialToOrderHandler::class)->handle(new AddMaterialToOrderCommand(
            orderId: $order->id,
            warehouseItemId: $warehouseItemId,
            quantity: $quantity,
        ));
    }

    public static function removeMaterial(OrderModel $order, int $materialId): void
    {
        OrderMaterialModel::query()
            ->where('order_id', $order->id)
            ->whereKey($materialId)
            ->delete();
    }
}
