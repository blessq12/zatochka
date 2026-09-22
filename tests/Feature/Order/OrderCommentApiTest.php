<?php

namespace Tests\Feature\Order;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderCommentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_adds_comment_and_sees_it_on_show(): void
    {
        $token = $this->tokenAsManager('cmt-mgr@example.com');
        $clientId = $this->createClient($token, 'cmt-client@example.com');
        $orderId = $this->createOrder($token, $clientId);

        $created = $this->withToken($token)->postJson("/api/orders/{$orderId}/comments", [
            'body' => 'Согласовать доставку',
        ])->assertCreated()
            ->assertJsonPath('comments.0.body', 'Согласовать доставку')
            ->assertJsonPath('comments.0.author_type', 'managers')
            ->json();

        $this->assertArrayHasKey('id', $created['comments'][0]);
        $this->assertArrayHasKey('created_at', $created['comments'][0]);
        $this->assertSame('regular', $created['comments'][0]['kind']);
        $this->assertArrayNotHasKey('parent_id', $created['comments'][0]);
        $this->assertArrayNotHasKey('type', $created['comments'][0]);

        $this->withToken($token)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonCount(1, 'comments')
            ->assertJsonPath('comments.0.body', 'Согласовать доставку');
    }

    public function test_assigned_master_can_add_comment(): void
    {
        $token = $this->tokenAsManager('cmt-mgr2@example.com');
        $clientId = $this->createClient($token, 'cmt-client2@example.com');
        $masterId = $this->createMaster($token, 'cmt-master@example.com');
        $orderId = $this->createOrder($token, $clientId);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterId,
        ])->assertOk();

        $masterToken = $this->loginAs('cmt-master@example.com', 'masters');

        $this->withToken($masterToken)->postJson("/api/orders/{$orderId}/comments", [
            'body' => 'Нужны детали',
        ])->assertCreated()
            ->assertJsonPath('comments.0.author_type', 'masters')
            ->assertJsonPath('comments.0.author_id', $masterId)
            ->assertJsonPath('comments.0.body', 'Нужны детали');
    }

    public function test_unassigned_master_cannot_comment(): void
    {
        $token = $this->tokenAsManager('cmt-mgr3@example.com');
        $clientId = $this->createClient($token, 'cmt-client3@example.com');
        $this->createMaster($token, 'cmt-master-a@example.com');
        $masterBId = $this->createMaster($token, 'cmt-master-b@example.com');
        $orderId = $this->createOrder($token, $clientId);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/assign-master", [
            'master_id' => $masterBId,
        ])->assertOk();

        $masterAToken = $this->loginAs('cmt-master-a@example.com', 'masters');

        $this->withToken($masterAToken)->postJson("/api/orders/{$orderId}/comments", [
            'body' => 'Чужой заказ',
        ])->assertForbidden();
    }

    public function test_client_cannot_post_or_see_comments(): void
    {
        $token = $this->tokenAsManager('cmt-mgr4@example.com');
        $clientId = $this->createClient($token, 'cmt-client4@example.com');
        $orderId = $this->createOrder($token, $clientId);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/comments", [
            'body' => 'Внутреннее',
        ])->assertCreated();

        $clientToken = $this->loginAs('cmt-client4@example.com', 'clients');

        $this->withToken($clientToken)->postJson("/api/orders/{$orderId}/comments", [
            'body' => 'Хочу написать',
        ])->assertForbidden();

        $show = $this->withToken($clientToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->json();

        $this->assertArrayNotHasKey('comments', $show);
    }

    public function test_empty_body_returns_422(): void
    {
        $token = $this->tokenAsManager('cmt-mgr5@example.com');
        $clientId = $this->createClient($token, 'cmt-client5@example.com');
        $orderId = $this->createOrder($token, $clientId);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/comments", [
            'body' => '',
        ])->assertStatus(422);

        $this->withToken($token)->postJson("/api/orders/{$orderId}/comments", [
            'body' => '   ',
        ])->assertStatus(422);
    }

    private function createOrder(string $managerToken, int $clientId): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/orders', [
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
