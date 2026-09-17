<?php

namespace Tests\Feature\Order;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_creates_mixed_order_and_runs_fsm(): void
    {
        $token = $this->tokenAsManager('ord-mgr@example.com');
        $clientId = $this->createClient($token, 'ord-client@example.com');
        $masterId = $this->createMaster($token, 'ord-master@example.com');
        $equipmentId = $this->createEquipment($token, $clientId);

        $created = $this->withToken($token)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                [
                    'kind' => 'sharpening',
                    'title' => 'Кусачки',
                    'quantity' => 3,
                ],
                [
                    'kind' => 'repair',
                    'equipment_id' => $equipmentId,
                    'problem' => 'не крутит',
                ],
            ],
        ])->assertCreated()
            ->assertJson([
                'client_id' => $clientId,
                'billing_type' => 'paid',
                'urgency' => 'normal',
                'estimated_cost' => '1500.00',
                'needs_delivery' => false,
                'status' => 'created',
                'master_id' => null,
            ])
            ->json();

        $this->assertCount(2, $created['items']);
        $orderId = $created['id'];

        $this->withToken($token)->putJson("/api/orders/{$orderId}/items", [
            'items' => [
                [
                    'kind' => 'sharpening',
                    'title' => 'Ножницы',
                    'quantity' => 1,
                ],
            ],
        ])->assertOk()->assertJsonCount(1, 'items');

        $this->withToken($token)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk()->assertJson([
            'status' => 'master_assigned',
            'master_id' => $masterId,
        ]);

        $this->withToken($token)->putJson("/api/orders/{$orderId}/items", [
            'items' => [
                ['kind' => 'sharpening', 'title' => 'X', 'quantity' => 1],
            ],
        ])->assertStatus(422);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'in_progress',
        ])->assertOk()->assertJson(['status' => 'in_progress']);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'waiting_parts',
        ])->assertOk()->assertJson(['status' => 'waiting_parts']);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'in_progress',
        ])->assertOk();

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'ready',
        ])->assertOk();

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'issued',
        ])->assertOk()->assertJson(['status' => 'issued']);
    }

    public function test_cancel_only_from_early_statuses(): void
    {
        $token = $this->tokenAsManager('ord-cancel@example.com');
        $clientId = $this->createClient($token, 'ord-cancel-client@example.com');

        $orderId = $this->withToken($token)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'warranty',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Пилка', 'quantity' => 2],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'cancelled',
        ])->assertOk()->assertJson(['status' => 'cancelled']);
    }

    public function test_client_review_on_issued_order(): void
    {
        $managerToken = $this->tokenAsManager('ord-rev-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'ord-rev-client@example.com');
        $masterId = $this->createMaster($managerToken, 'ord-rev-master@example.com');

        $orderId = $this->issueOrder($managerToken, $clientId, $masterId);

        $clientToken = $this->postJson('/api/identity/login', [
            'email' => 'ord-rev-client@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])->assertOk()->json('token');

        $this->withToken($clientToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJson(['id' => $orderId, 'status' => 'issued']);

        $this->withToken($clientToken)->postJson("/api/orders/{$orderId}/review", [
            'rating' => 5,
            'text' => 'Отлично',
        ])->assertCreated()
            ->assertJsonPath('review.rating', 5);

        $this->withToken($clientToken)->postJson("/api/orders/{$orderId}/review", [
            'rating' => 4,
        ])->assertStatus(422);
    }

    public function test_client_cannot_review_foreign_or_non_issued(): void
    {
        $managerToken = $this->tokenAsManager('ord-acl-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'ord-acl-client@example.com');
        $otherId = $this->createClient($managerToken, 'ord-acl-other@example.com');

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $ownerToken = $this->postJson('/api/identity/login', [
            'email' => 'ord-acl-client@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])->assertOk()->json('token');

        $this->withToken($ownerToken)->postJson("/api/orders/{$orderId}/review", [
            'rating' => 5,
        ])->assertStatus(422);

        $otherToken = $this->postJson('/api/identity/login', [
            'email' => 'ord-acl-other@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])->assertOk()->json('token');

        $this->withToken($otherToken)->getJson("/api/orders/{$orderId}")
            ->assertForbidden();

        unset($otherId);
    }

    public function test_invalid_transition_rejected(): void
    {
        $token = $this->tokenAsManager('ord-bad-fsm@example.com');
        $clientId = $this->createClient($token, 'ord-bad-client@example.com');

        $orderId = $this->withToken($token)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'A', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'ready',
        ])->assertStatus(422);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'master_assigned',
        ])->assertStatus(422);
    }

    private function issueOrder(string $managerToken, int $clientId, int $masterId): int
    {
        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        foreach (['in_progress', 'ready', 'issued'] as $status) {
            $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
                'status' => $status,
            ])->assertOk();
        }

        return (int) $orderId;
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
