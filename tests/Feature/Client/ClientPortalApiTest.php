<?php

namespace Tests\Feature\Client;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientPortalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_lists_active_and_archive_without_cancelled(): void
    {
        $managerToken = $this->tokenAsManager('cli-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'cli-user@example.com');

        $activeId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '100.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Ножницы', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $cancelledId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '100.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson("/api/orders/{$cancelledId}/transition", [
            'status' => 'cancelled',
        ])->assertOk();

        $clientToken = $this->loginClient('cli-user@example.com');

        $active = $this->withToken($clientToken)
            ->getJson('/api/orders?scope=active')
            ->assertOk()
            ->json('data');

        $activeIds = array_column($active, 'id');
        $this->assertContains($activeId, $activeIds);
        $this->assertNotContains($cancelledId, $activeIds);

        $this->withToken($clientToken)
            ->getJson('/api/orders?scope=archive')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->withToken($clientToken)
            ->getJson("/api/orders/{$cancelledId}")
            ->assertNotFound();
    }

    public function test_client_creates_order_and_lists_own_equipment(): void
    {
        $managerToken = $this->tokenAsManager('cli-mgr2@example.com');
        $clientId = $this->createClient($managerToken, 'cli-user2@example.com');
        $otherId = $this->createClient($managerToken, 'cli-other@example.com');

        $ownEquipmentId = $this->withToken($managerToken)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [],
        ])->assertCreated()->json('id');

        $foreignEquipmentId = $this->withToken($managerToken)->postJson('/api/equipments', [
            'client_id' => $otherId,
            'name' => 'Чужой',
            'brand' => 'X',
            'type' => 'Y',
            'modules' => [],
        ])->assertCreated()->json('id');

        $clientToken = $this->loginClient('cli-user2@example.com');

        $equipments = $this->withToken($clientToken)
            ->getJson('/api/equipments')
            ->assertOk()
            ->json('data');

        $ids = array_column($equipments, 'id');
        $this->assertContains($ownEquipmentId, $ids);
        $this->assertNotContains($foreignEquipmentId, $ids);

        $this->withToken($clientToken)
            ->getJson("/api/equipments/{$foreignEquipmentId}")
            ->assertNotFound();

        $order = $this->withToken($clientToken)->postJson('/api/orders', [
            'billing_type' => 'paid',
            'urgency' => 'urgent',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 2],
                [
                    'kind' => 'repair',
                    'equipment_id' => $ownEquipmentId,
                    'problem' => 'шум',
                ],
            ],
        ])->assertCreated()
            ->assertJson([
                'client_id' => $clientId,
                'status' => 'created',
                'urgency' => 'urgent',
            ])
            ->json();

        $this->assertSame($clientId, $order['client_id']);

        $this->withToken($clientToken)->postJson('/api/orders', [
            'billing_type' => 'paid',
            'needs_delivery' => false,
            'items' => [
                [
                    'kind' => 'repair',
                    'equipment_id' => $foreignEquipmentId,
                ],
            ],
        ])->assertStatus(422);
    }

    private function createClient(string $managerToken, string $email): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/actors/clients', [
            'email' => $email,
            'password' => 'password123',
            'name' => 'Клиент',
        ])->assertCreated()->json('id');
    }

    private function loginClient(string $email): string
    {
        return (string) $this->postJson('/api/identity/login', [
            'email' => $email,
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])->assertOk()->json('token');
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
