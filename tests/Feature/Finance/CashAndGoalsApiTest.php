<?php

namespace Tests\Feature\Finance;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CashAndGoalsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_records_manual_cash_and_goals(): void
    {
        $managerToken = $this->tokenAsManager('cash-mgr@example.com');

        $this->withToken($managerToken)->postJson('/api/finance/cash-entries', [
            'type' => 'income',
            'amount' => '1000',
            'comment' => 'Взнос',
        ])->assertCreated()
            ->assertJsonPath('type', 'income')
            ->assertJsonPath('source', 'manual')
            ->assertJsonPath('amount', '1000.00');

        $expense = $this->withToken($managerToken)->postJson('/api/finance/cash-entries', [
            'type' => 'expense',
            'amount' => '250.5',
            'comment' => 'Аренда',
        ])->assertCreated()->json();

        $list = $this->withToken($managerToken)->getJson('/api/finance/cash-entries')
            ->assertOk()
            ->assertJsonPath('summary.income', '1000.00')
            ->assertJsonPath('summary.expense', '250.50')
            ->assertJsonPath('summary.net', '749.50')
            ->json();

        $this->assertCount(2, $list['items']);

        $this->withToken($managerToken)->deleteJson('/api/finance/cash-entries/'.$expense['id'])
            ->assertNoContent();

        $this->withToken($managerToken)->getJson('/api/finance/cash-entries')
            ->assertOk()
            ->assertJsonPath('summary.net', '1000.00')
            ->assertJsonCount(1, 'items');

        $goal = $this->withToken($managerToken)->postJson('/api/finance/goals', [
            'title' => 'Месяц',
            'target_amount' => '5000',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ])->assertCreated()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('progress.net', '1000.00')
            ->json();

        $this->withToken($managerToken)->getJson('/api/finance/goals')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($managerToken)->postJson('/api/finance/goals/'.$goal['id'].'/cancel')
            ->assertOk()
            ->assertJsonPath('status', 'cancelled');
    }

    public function test_order_issued_creates_cash_income_from_pricing(): void
    {
        $managerToken = $this->tokenAsManager('issue-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'issue-client@example.com');

        $this->withToken($managerToken)->postJson('/api/actors/masters', [
            'email' => 'issue-master@example.com',
            'password' => 'password123',
            'name' => 'Мастер',
        ])->assertCreated();

        $masterToken = $this->postJson('/api/identity/login', [
            'email' => 'issue-master@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '500.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $order = $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")->json();
        $itemId = (int) $order['items'][0]['id'];
        $masterId = (int) $this->withToken($managerToken)->getJson('/api/actors/masters')->json('data.0.id');

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        $jobId = $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => [$itemId],
        ])->assertCreated()->json('id');

        $job = $this->withToken($masterToken)->putJson("/api/workshop/jobs/{$jobId}/items/{$itemId}", [
            'completed_qty' => 1,
            'works' => [['title' => 'Заточка']],
        ])->assertOk()->json();

        $workId = (int) $job['items'][0]['works'][0]['id'];

        $this->withToken($masterToken)->postJson("/api/workshop/jobs/{$jobId}/complete")
            ->assertOk();

        $this->withToken($managerToken)->putJson("/api/finance/pricings/by-order/{$orderId}", [
            'lines' => [
                ['work_entry_id' => $workId, 'amount' => '400'],
            ],
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'ready',
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'issued',
        ])->assertOk();

        $this->withToken($managerToken)->getJson('/api/finance/cash-entries')
            ->assertOk()
            ->assertJsonPath('summary.income', '400.00')
            ->assertJsonPath('items.0.source', 'order_issue')
            ->assertJsonPath('items.0.order_id', $orderId);
    }

    public function test_warranty_issue_records_zero_income(): void
    {
        $managerToken = $this->tokenAsManager('war-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'war-client@example.com');

        $this->withToken($managerToken)->postJson('/api/actors/masters', [
            'email' => 'war-master@example.com',
            'password' => 'password123',
            'name' => 'Мастер',
        ])->assertCreated();

        $masterToken = $this->postJson('/api/identity/login', [
            'email' => 'war-master@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');

        $orderId = $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'warranty',
            'urgency' => 'normal',
            'estimated_cost' => '0',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 1],
            ],
        ])->assertCreated()->json('id');

        $order = $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")->json();
        $itemId = (int) $order['items'][0]['id'];
        $masterId = (int) $this->withToken($managerToken)->getJson('/api/actors/masters')->json('data.0.id');

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        $jobId = $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => [$itemId],
        ])->assertCreated()->json('id');

        $this->withToken($masterToken)->putJson("/api/workshop/jobs/{$jobId}/items/{$itemId}", [
            'completed_qty' => 1,
            'works' => [['title' => 'Гарантия']],
        ])->assertOk();

        $this->withToken($masterToken)->postJson("/api/workshop/jobs/{$jobId}/complete")
            ->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'ready',
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'issued',
        ])->assertOk();

        $this->withToken($managerToken)->getJson('/api/finance/cash-entries')
            ->assertOk()
            ->assertJsonPath('summary.income', '0.00')
            ->assertJsonPath('items.0.source', 'order_issue');
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
