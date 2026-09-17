<?php

namespace Tests\Feature\Workshop;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorkshopFullCycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_cycle_manager_sees_master_work_titles(): void
    {
        $managerToken = $this->tokenAsManager('cycle-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'cycle-client@example.com');
        $masterId = $this->createMaster($managerToken, 'cycle-master@example.com');
        $equipmentId = $this->createEquipment($managerToken, $clientId);

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 7],
                ['kind' => 'repair', 'equipment_id' => $equipmentId, 'problem' => 'шум'],
            ],
        ])->assertCreated()->json('id');

        $idsAfterCreate = $this->orderItemIds($managerToken, $orderId);

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk()->assertJson(['status' => 'master_assigned']);

        $idsAfterAssign = $this->orderItemIds($managerToken, $orderId);
        $this->assertSame($idsAfterCreate, $idsAfterAssign, 'item ids must stay stable after assign');

        $masterToken = $this->loginMaster('cycle-master@example.com');

        $assigned = $this->withToken($masterToken)->getJson('/api/orders/assigned')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->json('data.0');

        $this->assertSame($orderId, $assigned['id']);
        $acceptItemIds = array_map(static fn (array $item): int => (int) $item['id'], $assigned['items']);
        $this->assertSame($idsAfterAssign, $acceptItemIds);

        $job = $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => $acceptItemIds,
        ])->assertCreated()->json();

        $idsAfterAccept = $this->orderItemIds($managerToken, $orderId);
        $this->assertSame($idsAfterAssign, $idsAfterAccept, 'item ids must stay stable after accept');

        $orderAsMaster = $this->withToken($masterToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJson(['status' => 'in_progress'])
            ->json();

        $sharpeningId = (int) collect($orderAsMaster['items'])->firstWhere('kind', 'sharpening')['id'];
        $repairId = (int) collect($orderAsMaster['items'])->firstWhere('kind', 'repair')['id'];

        $this->assertContains($sharpeningId, array_map('intval', array_column($job['items'], 'order_item_id')));
        $this->assertContains($repairId, array_map('intval', array_column($job['items'], 'order_item_id')));

        $jobId = (int) $job['id'];

        $updated = $this->withToken($masterToken)->putJson("/api/workshop/jobs/{$jobId}/items/{$sharpeningId}", [
            'completed_qty' => 5,
            'works' => [
                ['title' => 'Заточка режущей кромки'],
                ['title' => 'Полировка'],
            ],
        ])->assertOk()->json();

        $sharpeningJobItem = collect($updated['items'])->firstWhere('order_item_id', $sharpeningId);
        $this->assertNotNull($sharpeningJobItem);
        $this->assertSame(5, $sharpeningJobItem['completed_qty']);
        $this->assertSame(
            ['Заточка режущей кромки', 'Полировка'],
            array_column($sharpeningJobItem['works'], 'title'),
        );

        $this->withToken($masterToken)->putJson("/api/workshop/jobs/{$jobId}/items/{$repairId}", [
            'works' => [
                ['title' => 'Диагностика'],
                ['title' => 'Замена подшипника'],
            ],
        ])->assertOk();

        $this->withToken($masterToken)->postJson("/api/workshop/jobs/{$jobId}/complete")
            ->assertOk()
            ->assertJson(['status' => 'completed']);

        $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJson(['status' => 'works_completed']);

        $idsAfterComplete = $this->orderItemIds($managerToken, $orderId);
        $this->assertSame($idsAfterAssign, $idsAfterComplete, 'item ids must stay stable after complete');

        $workshopForManager = $this->withToken($managerToken)
            ->getJson("/api/workshop/jobs/by-order/{$orderId}")
            ->assertOk()
            ->json();

        $byOrderItemId = [];
        foreach ($workshopForManager['items'] as $item) {
            $byOrderItemId[(int) $item['order_item_id']] = $item;
        }

        $this->assertArrayHasKey($sharpeningId, $byOrderItemId);
        $this->assertArrayHasKey($repairId, $byOrderItemId);

        $this->assertSame(
            ['Заточка режущей кромки', 'Полировка'],
            array_column($byOrderItemId[$sharpeningId]['works'], 'title'),
        );
        $this->assertSame(
            ['Диагностика', 'Замена подшипника'],
            array_column($byOrderItemId[$repairId]['works'], 'title'),
        );

        $orderItems = $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")->json('items');
        $orderItemIds = array_map(static fn (array $item): int => (int) $item['id'], $orderItems);
        foreach (array_keys($byOrderItemId) as $workshopOrderItemId) {
            $this->assertContains(
                $workshopOrderItemId,
                $orderItemIds,
                'manager must resolve workshop order_item_id in order items',
            );
        }
    }

    /**
     * @return list<int>
     */
    private function orderItemIds(string $token, int $orderId): array
    {
        $order = $this->withToken($token)->getJson("/api/orders/{$orderId}")->assertOk()->json();

        return array_map(static fn (array $item): int => (int) $item['id'], $order['items']);
    }

    private function loginMaster(string $email): string
    {
        return (string) $this->postJson('/api/identity/login', [
            'email' => $email,
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');
    }

    private function createEquipment(string $managerToken, int $clientId): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [],
        ])->assertCreated()->json('id');
    }

    private function createClient(string $managerToken, string $email): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/actors/clients', [
            'email' => $email,
            'password' => 'password123',
            'name' => 'Клиент',
        ])->assertCreated()->json('id');
    }

    private function createMaster(string $managerToken, string $email): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/actors/masters', [
            'email' => $email,
            'password' => 'password123',
            'name' => 'Мастер',
        ])->assertCreated()->json('id');
    }

    private function tokenAsManager(string $email): string
    {
        $registered = $this->app->make(RegisterActorWithIdentityHandler::class)->handle(
            'managers',
            $email,
            'password123',
            issueToken: true,
            name: 'Тест Менеджер',
        );

        return (string) $registered->token;
    }
}
