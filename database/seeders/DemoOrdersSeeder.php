<?php

namespace Database\Seeders;

use App\Application\Finance\Command\ReplaceOrderMaterialLinesHandler;
use App\Application\Finance\Command\UpsertOrderPricingHandler;
use App\Application\Order\Command\AssignMasterHandler;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\Command\TransitionOrderStatusHandler;
use App\Application\Order\DTO\OrderResponse;
use App\Application\Order\Query\GetOrderHandler;
use App\Application\Warehouse\Command\ReplaceOrderIssueHandler;
use App\Application\Workshop\Command\AcceptWorkshopJobHandler;
use App\Application\Workshop\Command\CompleteWorkshopJobHandler;
use App\Application\Workshop\Command\UpdateWorkshopItemWorkHandler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Application\Workshop\Query\GetWorkshopJobHandler;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Infrastructure\Crm\Eloquent\EquipmentModel;
use App\Infrastructure\Order\Eloquent\OrderModel;
use App\Infrastructure\Warehouse\Eloquent\StockItemModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Order + Workshop + Finance + Warehouse issue: demo orders across FSM.
 */
final class DemoOrdersSeeder extends Seeder
{
    public function run(
        CreateOrderHandler $createOrder,
        AssignMasterHandler $assignMaster,
        AcceptWorkshopJobHandler $acceptJob,
        UpdateWorkshopItemWorkHandler $updateItemWork,
        CompleteWorkshopJobHandler $completeJob,
        UpsertOrderPricingHandler $upsertPricing,
        ReplaceOrderIssueHandler $replaceIssue,
        ReplaceOrderMaterialLinesHandler $replaceMaterials,
        TransitionOrderStatusHandler $transition,
        GetOrderHandler $getOrder,
        GetWorkshopJobHandler $getJob,
        IdentityRepository $identities,
        IdentityActorLinkRepository $links,
    ): void {
        if (OrderModel::query()->exists()) {
            $this->command?->info('Orders already seeded.');

            return;
        }

        $clientId = $this->actorId($identities, $links, SeedAccounts::clientEmail());
        $masterId = $this->actorId($identities, $links, SeedAccounts::masterEmail());
        $equipmentId = (int) EquipmentModel::query()->value('id');
        if ($equipmentId < 1) {
            throw new \RuntimeException('Equipment missing. Run EquipmentSeeder first.');
        }

        $stockBearing = (int) StockItemModel::query()->where('name', 'Подшипник 6202')->value('id');
        $stockPaste = (int) StockItemModel::query()->where('name', 'Абразивная паста')->value('id');
        if ($stockBearing < 1 || $stockPaste < 1) {
            throw new \RuntimeException('Warehouse items missing. Run WarehouseSeeder first.');
        }

        $created = $createOrder->handle(
            $clientId,
            'paid',
            'normal',
            '1200.00',
            false,
            null,
            [
                ['kind' => 'sharpening', 'title' => 'Кусачки маникюрные', 'quantity' => 5],
            ],
        );
        $this->command?->info("Order #{$created->id} status=created");

        $assigned = $createOrder->handle(
            $clientId,
            'paid',
            'urgent',
            '2500.00',
            true,
            'ул. Демо, 1',
            [
                ['kind' => 'sharpening', 'title' => 'Ножницы прямые', 'quantity' => 3],
                ['kind' => 'repair', 'equipment_id' => $equipmentId, 'problem' => 'Люфт шпинделя'],
            ],
        );
        $assignMaster->handle($assigned->id, $masterId);
        $this->command?->info("Order #{$assigned->id} status=master_assigned");

        $inProgress = $createOrder->handle(
            $clientId,
            'warranty',
            'normal',
            '1800.00',
            false,
            null,
            [
                ['kind' => 'sharpening', 'title' => 'Нож кухонный', 'quantity' => 4],
            ],
        );
        $assignMaster->handle($inProgress->id, $masterId);
        $inProgressOrder = $this->freshOrder($getOrder, $inProgress->id);
        $jobOpen = $acceptJob->handle(
            $inProgressOrder->id,
            $masterId,
            $this->itemIds($inProgressOrder),
        );
        $sharpeningOpenId = $this->firstItemId($inProgressOrder, 'sharpening');
        $updateItemWork->handle(
            $jobOpen->id,
            $sharpeningOpenId,
            $masterId,
            2,
            ['Черновая заточка'],
            4,
        );
        $this->command?->info("Order #{$inProgressOrder->id} status=in_progress job=#{$jobOpen->id}");

        $worksDone = $this->seedCompletedOrder(
            $createOrder,
            $assignMaster,
            $acceptJob,
            $updateItemWork,
            $completeJob,
            $getOrder,
            $getJob,
            $clientId,
            $masterId,
            [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 6],
                ['kind' => 'repair', 'equipment_id' => $equipmentId, 'problem' => 'Шум подшипника'],
            ],
            '3200.00',
            'paid',
            'normal',
        );

        $upsertPricing->handle($worksDone['order']->id, $this->pricingLines(
            $worksDone['work_ids'],
            ['900.00', '450.00', '800.00', '700.00'],
        ));

        DB::transaction(function () use (
            $replaceIssue,
            $replaceMaterials,
            $worksDone,
            $stockBearing,
            $stockPaste,
        ): void {
            $replaceIssue->handle($worksDone['order']->id, [
                ['stock_item_id' => $stockBearing, 'qty' => '2'],
                ['stock_item_id' => $stockPaste, 'qty' => '0.5'],
            ]);
            $replaceMaterials->handle($worksDone['order']->id, [
                ['stock_item_id' => $stockBearing, 'amount' => '400.00'],
                ['stock_item_id' => $stockPaste, 'amount' => '250.00'],
            ]);
        });
        $this->command?->info("Order #{$worksDone['order']->id} status=works_completed (priced + materials)");

        $ready = $this->seedCompletedOrder(
            $createOrder,
            $assignMaster,
            $acceptJob,
            $updateItemWork,
            $completeJob,
            $getOrder,
            $getJob,
            $clientId,
            $masterId,
            [
                ['kind' => 'sharpening', 'title' => 'Пинцет', 'quantity' => 2],
            ],
            '800.00',
            'paid',
            'normal',
        );
        $upsertPricing->handle($ready['order']->id, $this->pricingLines(
            $ready['work_ids'],
            ['350.00', '250.00'],
        ));
        $transition->handle($ready['order']->id, 'ready');
        $this->command?->info("Order #{$ready['order']->id} status=ready");
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{order: OrderResponse, sharpening_id: int, repair_id: int|null, work_ids: list<int>}
     */
    private function seedCompletedOrder(
        CreateOrderHandler $createOrder,
        AssignMasterHandler $assignMaster,
        AcceptWorkshopJobHandler $acceptJob,
        UpdateWorkshopItemWorkHandler $updateItemWork,
        CompleteWorkshopJobHandler $completeJob,
        GetOrderHandler $getOrder,
        GetWorkshopJobHandler $getJob,
        int $clientId,
        int $masterId,
        array $items,
        string $estimatedCost,
        string $billing,
        string $urgency,
    ): array {
        $order = $createOrder->handle(
            $clientId,
            $billing,
            $urgency,
            $estimatedCost,
            false,
            null,
            $items,
        );
        $assignMaster->handle($order->id, $masterId);
        $order = $this->freshOrder($getOrder, $order->id);

        $job = $acceptJob->handle($order->id, $masterId, $this->itemIds($order));
        $sharpeningId = $this->firstItemId($order, 'sharpening');
        $repairId = $this->firstItemIdOrNull($order, 'repair');

        $qty = null;
        foreach ($order->items as $item) {
            if ($item['kind'] === 'sharpening' && (int) $item['id'] === $sharpeningId) {
                $qty = (int) $item['quantity'];
            }
        }

        $updateItemWork->handle(
            $job->id,
            $sharpeningId,
            $masterId,
            $qty,
            ['Заточка', 'Полировка'],
            $qty,
        );

        if ($repairId !== null) {
            $updateItemWork->handle(
                $job->id,
                $repairId,
                $masterId,
                null,
                ['Диагностика', 'Замена узла'],
            );
        }

        $completeJob->handle($job->id, $masterId);
        $order = $this->freshOrder($getOrder, $order->id);
        $job = $this->freshJob($getJob, $job->id);

        return [
            'order' => $order,
            'sharpening_id' => $sharpeningId,
            'repair_id' => $repairId,
            'work_ids' => $this->workEntryIds($job),
        ];
    }

    private function freshOrder(GetOrderHandler $getOrder, int $orderId): OrderResponse
    {
        $order = $getOrder->handle($orderId);
        if ($order === null) {
            throw new \RuntimeException("Order #{$orderId} not found after seed step.");
        }

        return $order;
    }

    private function freshJob(GetWorkshopJobHandler $getJob, int $jobId): WorkshopJobResponse
    {
        $job = $getJob->handle($jobId);
        if ($job === null) {
            throw new \RuntimeException("Workshop job #{$jobId} not found after seed step.");
        }

        return $job;
    }

    /**
     * @return list<int>
     */
    private function workEntryIds(WorkshopJobResponse $job): array
    {
        $ids = [];
        foreach ($job->items as $item) {
            foreach ($item['works'] ?? [] as $work) {
                $ids[] = (int) $work['id'];
            }
        }

        return $ids;
    }

    /**
     * @param  list<int>  $workIds
     * @param  list<string>  $amounts
     * @return list<array{work_entry_id: int, amount: string}>
     */
    private function pricingLines(array $workIds, array $amounts): array
    {
        if (count($workIds) !== count($amounts)) {
            throw new \RuntimeException('Work ids and amounts count mismatch in seed pricing.');
        }

        $lines = [];
        foreach ($workIds as $index => $workId) {
            $lines[] = [
                'work_entry_id' => $workId,
                'amount' => $amounts[$index],
            ];
        }

        return $lines;
    }

    /**
     * @return list<int>
     */
    private function itemIds(OrderResponse $order): array
    {
        return array_map(
            static fn (array $item): int => (int) $item['id'],
            $order->items,
        );
    }

    private function firstItemId(OrderResponse $order, string $kind): int
    {
        $id = $this->firstItemIdOrNull($order, $kind);
        if ($id === null) {
            throw new \RuntimeException("Order #{$order->id} has no {$kind} item.");
        }

        return $id;
    }

    private function firstItemIdOrNull(OrderResponse $order, string $kind): ?int
    {
        foreach ($order->items as $item) {
            if ($item['kind'] === $kind) {
                return (int) $item['id'];
            }
        }

        return null;
    }

    private function actorId(
        IdentityRepository $identities,
        IdentityActorLinkRepository $links,
        string $email,
    ): int {
        $identity = $identities->findByEmail($email);
        if ($identity === null) {
            throw new \RuntimeException("Seed actor not found: {$email}");
        }
        $link = $links->findByIdentityId((int) $identity->id());
        if ($link === null) {
            throw new \RuntimeException("No actor link for {$email}");
        }

        return $link->actorId;
    }
}
