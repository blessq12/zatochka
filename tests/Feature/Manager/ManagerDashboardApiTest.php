<?php

namespace Tests\Feature\Manager;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ManagerDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_reads_dashboard_snapshot(): void
    {
        $managerToken = $this->tokenAsManager('dash-mgr@example.com');
        $clientId = (int) $this->withToken($managerToken)->postJson('/api/actors/clients', [
            'email' => 'dash-client@example.com',
            'password' => 'password123',
            'name' => 'Клиент',
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '100.00',
            'needs_delivery' => false,
            'items' => [
                ['kind' => 'sharpening', 'title' => 'Нож', 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->withToken($managerToken)->postJson('/api/finance/cash-entries', [
            'type' => 'income',
            'amount' => '500',
        ])->assertCreated();

        $this->withToken($managerToken)->postJson('/api/finance/goals', [
            'title' => 'Квартал',
            'target_amount' => '10000',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ])->assertCreated();

        $this->withToken($managerToken)->getJson('/api/manager/dashboard')
            ->assertOk()
            ->assertJsonPath('attention.created', 1)
            ->assertJsonPath('attention.works_completed', 0)
            ->assertJsonPath('finance.balance', '500.00')
            ->assertJsonPath('goals.0.title', 'Квартал')
            ->assertJsonPath('goals.0.net', '500.00');
    }

    public function test_non_manager_forbidden(): void
    {
        $managerToken = $this->tokenAsManager('dash-acl-mgr@example.com');
        $this->withToken($managerToken)->postJson('/api/actors/masters', [
            'email' => 'dash-master@example.com',
            'password' => 'password123',
            'name' => 'Мастер',
        ])->assertCreated();

        $masterToken = $this->postJson('/api/identity/login', [
            'email' => 'dash-master@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');

        $this->withToken($masterToken)->getJson('/api/manager/dashboard')
            ->assertForbidden();
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
