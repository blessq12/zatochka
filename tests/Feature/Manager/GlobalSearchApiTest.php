<?php

namespace Tests\Feature\Manager;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_clients_by_name_and_phone_case_insensitive(): void
    {
        $token = $this->tokenAsManager('search-mgr@example.com');

        $this->withToken($token)->postJson('/api/actors/clients', [
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'name' => 'Иван Петров',
            'phone' => '+7 (999) 111-22-33',
        ])->assertCreated();

        $byName = $this->withToken($token)->getJson('/api/manager/search?q=иван')
            ->assertOk()
            ->json();

        $this->assertCount(1, $byName['clients']);
        $this->assertSame('Иван Петров', $byName['clients'][0]['name']);
        $this->assertSame([], $byName['equipments']);

        $byPhone = $this->withToken($token)->getJson('/api/manager/search?q=999111')
            ->assertOk()
            ->json();

        $this->assertCount(1, $byPhone['clients']);
        $this->assertSame('Иван Петров', $byPhone['clients'][0]['name']);
    }

    public function test_search_equipment_by_name_and_module_serial(): void
    {
        $token = $this->tokenAsManager('search-eq-mgr@example.com');
        $clientId = (int) $this->withToken($token)->postJson('/api/actors/clients', [
            'email' => 'eq-owner@example.com',
            'password' => 'password123',
            'name' => 'Владелец',
            'phone' => '+79990000001',
        ])->assertCreated()->json('id');

        $equipmentId = (int) $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Машинка Wahl',
            'brand' => 'Wahl',
            'type' => 'Машинка',
            'modules' => [
                ['name' => 'Нож', 'serial_number' => 'SN-ABC-42'],
            ],
        ])->assertCreated()->json('id');

        $byName = $this->withToken($token)->getJson('/api/manager/search?q=wahl')
            ->assertOk()
            ->json();

        $this->assertCount(1, $byName['equipments']);
        $this->assertSame($equipmentId, $byName['equipments'][0]['id']);

        $bySerial = $this->withToken($token)->getJson('/api/manager/search?q=sn-abc')
            ->assertOk()
            ->json();

        $this->assertCount(1, $bySerial['equipments']);
        $this->assertSame($equipmentId, $bySerial['equipments'][0]['id']);
    }

    public function test_short_query_returns_empty_and_master_forbidden(): void
    {
        $token = $this->tokenAsManager('search-short-mgr@example.com');
        $this->createMaster($token, 'search-master@example.com');

        $this->withToken($token)->getJson('/api/manager/search?q=а')
            ->assertOk()
            ->assertJson([
                'clients' => [],
                'equipments' => [],
            ]);

        $masterToken = $this->loginAs('search-master@example.com', 'masters');
        $this->withToken($masterToken)->getJson('/api/manager/search?q=тест')
            ->assertForbidden();
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
