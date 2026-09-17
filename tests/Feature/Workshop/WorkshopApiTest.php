<?php

namespace Tests\Feature\Workshop;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorkshopApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_accept_update_and_complete_workshop_job(): void
    {
        $managerToken = $this->tokenAsManager('ws-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'ws-client@example.com');
        $masterId = $this->createMaster($managerToken, 'ws-master@example.com');

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1000',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 7],
                ['kind' => 'repair', 'equipment_id' => $this->createEquipment($managerToken, $clientId), 'problem' => 'шум'],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        $order = $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")->json();
        $itemIds = array_map(static fn (array $item): int => (int) $item['id'], $order['items']);

        $masterToken = $this->loginMaster('ws-master@example.com');

        $this->withToken($masterToken)->getJson('/api/orders/assigned')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $job = $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => $itemIds,
        ])->assertCreated()
            ->assertJson([
                'order_id' => $orderId,
                'master_id' => $masterId,
                'status' => 'open',
            ])
            ->json();

        $orderAfterAccept = $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJson(['status' => 'in_progress'])
            ->json();

        $itemIdsAfterAccept = array_map(
            static fn (array $item): int => (int) $item['id'],
            $orderAfterAccept['items'],
        );
        $this->assertSame($itemIds, $itemIdsAfterAccept);

        $sharpeningItemIdAfterAccept = (int) collect($orderAfterAccept['items'])
            ->firstWhere('kind', 'sharpening')['id'];

        $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => $itemIds,
        ])->assertStatus(422);

        $jobId = $job['id'];

        $this->withToken($masterToken)->putJson(
            "/api/workshop/jobs/{$jobId}/items/{$sharpeningItemIdAfterAccept}",
            [
                'completed_qty' => 5,
                'works' => [
                    ['title' => 'Заточка режущей кромки'],
                    ['title' => 'Полировка'],
                ],
            ],
        )->assertOk()
            ->assertJsonPath('items.0.completed_qty', 5)
            ->assertJsonPath('items.0.works.0.title', 'Заточка режущей кромки')
            ->assertJsonPath('items.0.works.1.title', 'Полировка');

        $this->withToken($managerToken)->getJson("/api/workshop/jobs/by-order/{$orderId}")
            ->assertOk()
            ->assertJsonPath('items.0.works.0.title', 'Заточка режущей кромки')
            ->assertJsonPath('items.0.works.1.title', 'Полировка');

        $this->withToken($masterToken)->postJson("/api/workshop/jobs/{$jobId}/complete")
            ->assertOk()
            ->assertJson(['status' => 'completed'])
            ->assertJsonPath('items.0.works.0.title', 'Заточка режущей кромки');

        $this->withToken($managerToken)->getJson("/api/workshop/jobs/by-order/{$orderId}")
            ->assertOk()
            ->assertJsonPath('items.0.works.0.title', 'Заточка режущей кромки')
            ->assertJsonPath('items.0.works.1.title', 'Полировка');

        $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
            ->assertJson(['status' => 'works_completed']);

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'ready',
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'issued',
        ])->assertOk();
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
