<?php

namespace Tests\Feature\Order;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderDraftApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_creates_pending_draft_without_client(): void
    {
        $draft = $this->postJson('/api/public/order-drafts', [
            'full_name' => 'Иван Тестов',
            'phone' => '+79001112233',
            'service_type' => 'sharpening',
            'comment' => 'нужна заточка',
            'intake_data' => [
                'tool_type' => 'scissors',
                'tools_count' => 2,
            ],
            'needs_delivery' => false,
        ])->assertCreated()
            ->assertJson([
                'source' => 'public',
                'status' => 'pending',
                'client_id' => null,
                'full_name' => 'Иван Тестов',
                'phone' => '+79001112233',
                'service_type' => 'sharpening',
                'order_id' => null,
            ])
            ->json();

        $this->assertSame('scissors', $draft['payload']['intake_data']['tool_type']);
    }

    public function test_client_creates_updates_and_cancels_own_draft(): void
    {
        $managerToken = $this->tokenAsManager('draft-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'draft-client@example.com');
        $clientToken = $this->loginClient('draft-client@example.com');

        $draftId = $this->withToken($clientToken)->postJson('/api/order-drafts', [
            'service_type' => 'sharpening',
            'needs_delivery' => false,
            'payload' => [
                'items' => [
                    ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 2],
                ],
            ],
        ])->assertCreated()
            ->assertJson([
                'source' => 'client_lk',
                'status' => 'pending',
                'client_id' => $clientId,
                'service_type' => 'sharpening',
            ])
            ->json('id');

        $this->withToken($clientToken)->putJson("/api/order-drafts/{$draftId}", [
            'service_type' => 'sharpening',
            'needs_delivery' => true,
            'delivery_address' => 'ул. Тестовая, 1',
            'payload' => [
                'items' => [
                    ['kind' => 'sharpening', 'title' => 'Ножницы', 'quantity' => 1],
                ],
            ],
        ])->assertOk()
            ->assertJson([
                'needs_delivery' => true,
                'delivery_address' => 'ул. Тестовая, 1',
            ]);

        $this->withToken($clientToken)->postJson("/api/order-drafts/{$draftId}/cancel")
            ->assertOk()
            ->assertJson(['status' => 'cancelled']);

        $this->withToken($clientToken)->getJson("/api/order-drafts/{$draftId}")
            ->assertNotFound();
    }

    public function test_client_cannot_create_full_order(): void
    {
        $managerToken = $this->tokenAsManager('draft-noord-mgr@example.com');
        $this->createClient($managerToken, 'draft-noord-client@example.com');
        $clientToken = $this->loginClient('draft-noord-client@example.com');

        $this->withToken($clientToken)->postJson('/api/orders', [
            'billing_type' => 'paid',
            'estimated_cost' => '100',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'X', 'quantity' => 1],
            ],
        ])->assertForbidden();
    }

    public function test_manager_promotes_public_draft_with_create_client(): void
    {
        $managerToken = $this->tokenAsManager('draft-promote-mgr@example.com');

        $draftId = $this->postJson('/api/public/order-drafts', [
            'full_name' => 'Пётр Заявка',
            'phone' => '+79005556677',
            'service_type' => 'repair',
            'intake_data' => [
                'equipment_type' => 'dryer',
                'device_name' => 'Фен',
                'problem_description' => 'не греет',
            ],
            'needs_delivery' => false,
        ])->assertCreated()->json('id');

        $result = $this->withToken($managerToken)->postJson("/api/order-drafts/{$draftId}/promote", [
            'create_client' => true,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '2500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 1],
            ],
        ])->assertOk()
            ->assertJson([
                'draft' => [
                    'status' => 'promoted',
                ],
                'order' => [
                    'status' => 'created',
                    'billing_type' => 'paid',
                    'estimated_cost' => '2500.00',
                ],
            ])
            ->json();

        $this->assertNotNull($result['draft']['order_id']);
        $this->assertSame($result['order']['id'], $result['draft']['order_id']);
        $this->assertGreaterThan(0, $result['order']['client_id']);

        $this->withToken($managerToken)->postJson("/api/order-drafts/{$draftId}/promote", [
            'create_client' => true,
            'billing_type' => 'paid',
            'estimated_cost' => '1',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'X', 'quantity' => 1],
            ],
        ])->assertStatus(422);
    }

    public function test_manager_promotes_client_lk_draft(): void
    {
        $managerToken = $this->tokenAsManager('draft-lk-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'draft-lk-client@example.com');
        $clientToken = $this->loginClient('draft-lk-client@example.com');
        $equipmentId = $this->createEquipment($managerToken, $clientId);

        $draftId = $this->withToken($clientToken)->postJson('/api/order-drafts', [
            'service_type' => 'repair',
            'needs_delivery' => false,
            'payload' => [
                'items' => [
                    [
                        'kind' => 'repair',
                        'equipment_id' => $equipmentId,
                        'problem' => 'шум',
                    ],
                ],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson("/api/order-drafts/{$draftId}/promote", [
            'billing_type' => 'paid',
            'estimated_cost' => '900.00',
            'needs_delivery' => false,
            'items' => [
                [
                    'kind' => 'repair',
                    'equipment_id' => $equipmentId,
                    'problem' => 'шум',
                ],
            ],
        ])->assertOk()
            ->assertJson([
                'draft' => ['status' => 'promoted', 'client_id' => $clientId],
                'order' => ['status' => 'created', 'client_id' => $clientId],
            ]);
    }

    private function createClient(string $managerToken, string $email): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/actors/clients', [
            'email' => $email,
            'password' => 'password123',
            'name' => 'Клиент',
        ])->assertCreated()->json('id');
    }

    private function createEquipment(string $managerToken, int $clientId): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фен',
            'brand' => 'Test',
            'type' => 'dryer',
            'modules' => [
                ['name' => 'Мотор', 'serial_number' => 'SN-DRAFT-1'],
            ],
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
