<?php

namespace Tests\Feature\Finance;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FinanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_upserts_and_reads_order_pricing(): void
    {
        $managerToken = $this->tokenAsManager('fin-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'fin-client@example.com');

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Кусачки', 'quantity' => 2],
                ['kind' => 'sharpening', 'title' => 'Ножницы', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $workA = 101;
        $workB = 202;

        $this->withToken($managerToken)->getJson("/api/finance/pricings/by-order/{$orderId}")
            ->assertNotFound();

        $pricing = $this->withToken($managerToken)->putJson("/api/finance/pricings/by-order/{$orderId}", [
            'lines' => [
                ['work_entry_id' => $workA, 'amount' => '100.50'],
                ['work_entry_id' => $workB, 'amount' => '200.00'],
            ],
        ])->assertOk()
            ->assertJson([
                'order_id' => $orderId,
                'status' => 'priced',
                'total' => '300.50',
            ])
            ->assertJsonCount(2, 'lines')
            ->json();

        $this->assertSame($workA, $pricing['lines'][0]['work_entry_id']);
        $this->assertSame('100.50', $pricing['lines'][0]['amount']);

        $this->withToken($managerToken)->getJson("/api/finance/pricings/by-order/{$orderId}")
            ->assertOk()
            ->assertJson([
                'id' => $pricing['id'],
                'total' => '300.50',
            ]);

        $this->withToken($managerToken)->putJson("/api/finance/pricings/by-order/{$orderId}", [
            'lines' => [
                ['work_entry_id' => $workA, 'amount' => '50'],
            ],
        ])->assertOk()
            ->assertJson([
                'total' => '50.00',
            ])
            ->assertJsonCount(1, 'lines');
    }

    public function test_non_manager_forbidden(): void
    {
        $managerToken = $this->tokenAsManager('fin-acl-mgr@example.com');
        $this->withToken($managerToken)->postJson('/api/actors/masters', [
            'email' => 'fin-master@example.com',
            'password' => 'password123',
            'name' => 'Мастер',
        ])->assertCreated();

        $masterToken = $this->postJson('/api/identity/login', [
            'email' => 'fin-master@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');

        $this->withToken($masterToken)->getJson('/api/finance/pricings/by-order/1')
            ->assertForbidden();

        $this->withToken($masterToken)->putJson('/api/finance/pricings/by-order/1', [
            'lines' => [
                ['work_entry_id' => 1, 'amount' => '10'],
            ],
        ])->assertForbidden();
    }

    private function createClient(string $managerToken, string $email): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/actors/clients', [
            'email' => $email,
            'password' => 'password123',
            'name' => 'Клиент',
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
