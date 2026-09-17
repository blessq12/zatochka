<?php

namespace Tests\Feature\Warehouse;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WarehouseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_catalog_receive_and_apply_materials_on_works_completed(): void
    {
        $managerToken = $this->tokenAsManager('wh-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'wh-client@example.com');
        $masterId = $this->createMaster($managerToken, 'wh-master@example.com');

        $masterToken = $this->postJson('/api/identity/login', [
            'email' => 'wh-master@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');

        $item = $this->withToken($managerToken)->postJson('/api/warehouse/items', [
            'category' => 'spare_part',
            'name' => 'Подшипник',
            'unit' => 'шт',
            'qty_on_hand' => '10',
        ])->assertCreated()
            ->assertJson([
                'name' => 'Подшипник',
                'qty_on_hand' => '10.000',
            ])
            ->json();

        $stockItemId = (int) $item['id'];

        $this->withToken($managerToken)->postJson("/api/warehouse/items/{$stockItemId}/receive", [
            'qty' => '2.5',
        ])->assertOk()
            ->assertJson(['qty_on_hand' => '12.500']);

        $this->withToken($masterToken)->getJson('/api/warehouse/items')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($masterToken)->postJson('/api/warehouse/items', [
            'category' => 'consumable',
            'name' => 'Масло',
            'unit' => 'л',
        ])->assertForbidden();

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1000.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        $order = $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")->json();
        $orderItemId = (int) $order['items'][0]['id'];

        $jobId = $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => [$orderItemId],
        ])->assertCreated()->json('id');

        $job = $this->withToken($masterToken)->putJson("/api/workshop/jobs/{$jobId}/items/{$orderItemId}", [
            'completed_qty' => 1,
            'works' => [['title' => 'Заточка']],
        ])->assertOk()->json();

        $workEntryId = (int) $job['items'][0]['works'][0]['id'];

        $this->withToken($masterToken)->postJson("/api/workshop/jobs/{$jobId}/complete")
            ->assertOk();

        $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJson(['status' => 'works_completed']);

        $this->withToken($managerToken)->putJson("/api/orders/{$orderId}/materials", [
            'lines' => [
                [
                    'stock_item_id' => $stockItemId,
                    'qty' => '3',
                    'amount' => '150.00',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('issue.lines.0.stock_item_id', $stockItemId)
            ->assertJsonPath('pricing.material_lines.0.amount', '150.00')
            ->assertJsonPath('pricing.total', '150.00');

        $this->withToken($managerToken)->getJson("/api/warehouse/items/{$stockItemId}")
            ->assertOk()
            ->assertJson(['qty_on_hand' => '9.500']);

        $this->withToken($managerToken)->putJson("/api/finance/pricings/by-order/{$orderId}", [
            'lines' => [
                ['work_entry_id' => $workEntryId, 'amount' => '200'],
            ],
        ])->assertOk()
            ->assertJson([
                'total' => '350.00',
            ])
            ->assertJsonCount(1, 'material_lines');

        $this->withToken($managerToken)->putJson("/api/orders/{$orderId}/materials", [
            'lines' => [
                [
                    'stock_item_id' => $stockItemId,
                    'qty' => '100',
                    'amount' => '10',
                ],
            ],
        ])->assertStatus(422);
    }

    public function test_materials_forbidden_before_works_completed(): void
    {
        $managerToken = $this->tokenAsManager('wh-early-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'wh-early-client@example.com');

        $itemId = (int) $this->withToken($managerToken)->postJson('/api/warehouse/items', [
            'category' => 'consumable',
            'name' => 'Лента',
            'unit' => 'м',
            'qty_on_hand' => '5',
        ])->assertCreated()->json('id');

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '100.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->putJson("/api/orders/{$orderId}/materials", [
            'lines' => [
                ['stock_item_id' => $itemId, 'qty' => '1', 'amount' => '10'],
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
