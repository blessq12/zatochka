<?php

namespace Tests\Feature\Order;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderApprovalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_requests_approval_with_comment(): void
    {
        [$managerToken, $masterToken, $orderId] = $this->orderInProgress();

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/request-approval", [
            'body' => 'Согласовать замену модуля',
        ])->assertOk()
            ->assertJsonPath('status', 'approval')
            ->assertJsonPath('comments.0.body', 'Согласовать замену модуля')
            ->assertJsonPath('comments.0.author_type', 'masters')
            ->assertJsonPath('comments.0.kind', 'approval_request');

        $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('status', 'approval')
            ->assertJsonCount(1, 'comments');
    }

    public function test_manager_resolves_approval_to_in_progress_with_comment(): void
    {
        [$managerToken, $masterToken, $orderId] = $this->orderInProgress();

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/request-approval", [
            'body' => 'Нужно ОК клиента',
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/resolve-approval", [
            'status' => 'in_progress',
            'body' => 'Клиент согласился, продолжаем',
        ])->assertOk()
            ->assertJsonPath('status', 'in_progress')
            ->assertJsonPath('comments.1.body', 'Клиент согласился, продолжаем')
            ->assertJsonPath('comments.1.author_type', 'managers')
            ->assertJsonPath('comments.1.kind', 'approval_result');
    }

    public function test_manager_issues_from_approval_and_closes_workshop_job(): void
    {
        [$managerToken, $masterToken, $orderId, $jobId] = $this->orderInProgress(withJobId: true);

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/request-approval", [
            'body' => 'Клиент отказался от ремонта',
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/resolve-approval", [
            'status' => 'issued',
            'body' => 'Выдаём без работ',
        ])->assertOk()
            ->assertJsonPath('status', 'issued');

        $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('status', 'issued')
            ->assertJsonStructure(['issued_at']);

        $this->withToken($masterToken)->getJson("/api/workshop/jobs/{$jobId}")
            ->assertOk()
            ->assertJsonPath('status', 'completed');
    }

    public function test_empty_body_and_plain_transition_from_approval_rejected(): void
    {
        [$managerToken, $masterToken, $orderId] = $this->orderInProgress();

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/request-approval", [
            'body' => '   ',
        ])->assertStatus(422);

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/request-approval", [
            'body' => 'Ок',
        ])->assertOk();

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'in_progress',
        ])->assertStatus(422);

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/resolve-approval", [
            'status' => 'in_progress',
            'body' => '   ',
        ])->assertStatus(422);

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/transition", [
            'status' => 'approval',
        ])->assertStatus(422);
    }

    public function test_unassigned_master_cannot_request_approval(): void
    {
        [$managerToken, $masterToken, $orderId] = $this->orderInProgress();
        $this->createMaster($managerToken, 'apr-other-master@example.com');
        $otherToken = $this->loginAs('apr-other-master@example.com', 'masters');

        $this->withToken($otherToken)->postJson("/api/orders/{$orderId}/request-approval", [
            'body' => 'Чужой заказ',
        ])->assertForbidden();
    }

    public function test_comment_does_not_change_status(): void
    {
        [$managerToken, $masterToken, $orderId] = $this->orderInProgress();

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/comments", [
            'body' => 'Просто заметка',
        ])->assertCreated()
            ->assertJsonPath('status', 'in_progress')
            ->assertJsonPath('comments.0.body', 'Просто заметка')
            ->assertJsonPath('comments.0.kind', 'regular');
    }

    /**
     * @return array{0: string, 1: string, 2: int, 3?: int}
     */
    private function orderInProgress(bool $withJobId = false): array
    {
        $managerToken = $this->tokenAsManager('apr-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'apr-client@example.com');
        $masterId = $this->createMaster($managerToken, 'apr-master@example.com');

        $orderId = (int) $this->withToken($managerToken)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '1000.00',
            'needs_delivery' => false,
            'items' => [
                [
                    'kind' => 'sharpening',
                    'title' => 'Кусачки',
                    'quantity' => 1,
                ],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($managerToken)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        $itemIds = array_map(
            static fn (array $item): int => (int) $item['id'],
            $this->withToken($managerToken)->getJson("/api/orders/{$orderId}")
                ->assertOk()
                ->json('items'),
        );

        $masterToken = $this->loginAs('apr-master@example.com', 'masters');

        $jobId = (int) $this->withToken($masterToken)->postJson('/api/workshop/jobs/accept', [
            'order_id' => $orderId,
            'order_item_ids' => $itemIds,
        ])->assertCreated()->json('id');

        if ($withJobId) {
            return [$managerToken, $masterToken, $orderId, $jobId];
        }

        return [$managerToken, $masterToken, $orderId];
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

    private function loginAs(string $email, string $actorType): string
    {
        return (string) $this->postJson('/api/identity/login', [
            'email' => $email,
            'password' => 'password123',
            'expected_actor_type' => $actorType,
        ])->assertOk()->json('token');
    }
}
