<?php

namespace Tests\Feature\Crm;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EquipmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_crud_equipment_with_modules(): void
    {
        $token = $this->tokenAsManager('eq-mgr@example.com');
        $clientId = $this->createClient($token, 'client-eq@example.com', 'Клиент EQ');

        $created = $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Маникюрный аппарат',
            'modules' => [
                ['name' => 'Блок', 'serial_number' => 'BLK-1'],
                ['name' => 'Мотор', 'serial_number' => 'MTR-1'],
            ],
        ])->assertCreated()
            ->assertJson([
                'client_id' => $clientId,
                'client_name' => 'Клиент EQ',
                'name' => 'Фрезер',
                'brand' => 'Strong',
                'type' => 'Маникюрный аппарат',
            ])
            ->json();

        $this->assertCount(2, $created['modules']);
        $this->assertSame('BLK-1', $created['modules'][0]['serial_number']);

        $id = $created['id'];

        $this->withToken($token)->getJson('/api/equipments')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson("/api/equipments?client_id={$clientId}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson('/api/equipments?client_id=999999')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->withToken($token)->getJson("/api/equipments/{$id}")
            ->assertOk()
            ->assertJson(['id' => $id, 'name' => 'Фрезер']);

        $this->withToken($token)->putJson("/api/equipments/{$id}", [
            'name' => 'Фрезер 2',
            'brand' => 'Strong',
            'type' => 'Маникюрный аппарат',
            'modules' => [
                ['name' => 'Блок', 'serial_number' => 'BLK-2'],
            ],
        ])->assertOk()
            ->assertJson([
                'name' => 'Фрезер 2',
            ])
            ->assertJsonCount(1, 'modules');

        $this->withToken($token)->deleteJson("/api/equipments/{$id}")
            ->assertNoContent();

        $this->withToken($token)->getJson("/api/equipments/{$id}")
            ->assertNotFound();
    }

    public function test_equipment_without_modules_is_rejected(): void
    {
        $token = $this->tokenAsManager('eq-empty@example.com');
        $clientId = $this->createClient($token, 'client-empty@example.com', 'Клиент');

        $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фен',
            'brand' => 'Dyson',
            'type' => 'Фен',
            'modules' => [],
        ])->assertStatus(422);
    }

    public function test_update_cannot_clear_all_modules(): void
    {
        $token = $this->tokenAsManager('eq-clear@example.com');
        $clientId = $this->createClient($token, 'client-clear@example.com', 'Клиент');

        $id = (int) $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [
                ['name' => 'Блок', 'serial_number' => 'BLK-CLR'],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($token)->putJson("/api/equipments/{$id}", [
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [],
        ])->assertStatus(422);
    }

    public function test_update_preserves_module_ids(): void
    {
        $token = $this->tokenAsManager('eq-stable@example.com');
        $clientId = $this->createClient($token, 'client-stable@example.com', 'Клиент');

        $created = $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [
                ['name' => 'Блок', 'serial_number' => 'BLK-1'],
                ['name' => 'Мотор', 'serial_number' => 'MTR-1'],
            ],
        ])->assertCreated()->json();

        $moduleId = (int) $created['modules'][0]['id'];

        $updated = $this->withToken($token)->putJson("/api/equipments/{$created['id']}", [
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [
                ['id' => $moduleId, 'name' => 'Блок питания', 'serial_number' => 'BLK-1'],
                ['name' => 'Педаль', 'serial_number' => 'PDL-1'],
            ],
        ])->assertOk()->json();

        $preserved = collect($updated['modules'])->firstWhere('serial_number', 'BLK-1');
        $this->assertNotNull($preserved);
        $this->assertSame($moduleId, (int) $preserved['id']);
        $this->assertSame('Блок питания', $preserved['name']);
        $this->assertCount(2, $updated['modules']);
        $this->assertNull(collect($updated['modules'])->firstWhere('serial_number', 'MTR-1'));
    }

    public function test_duplicate_serial_within_equipment_rejected(): void
    {
        $token = $this->tokenAsManager('eq-dup@example.com');
        $clientId = $this->createClient($token, 'client-dup@example.com', 'Клиент');

        $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Аппарат',
            'brand' => 'Brand',
            'type' => 'Type',
            'modules' => [
                ['name' => 'A', 'serial_number' => 'SAME'],
                ['name' => 'B', 'serial_number' => 'SAME'],
            ],
        ])->assertStatus(422);
    }

    public function test_non_manager_forbidden(): void
    {
        $managerToken = $this->tokenAsManager('eq-owner@example.com');
        $clientId = $this->createClient($managerToken, 'eq-client@example.com', 'Клиент');

        $clientToken = $this->postJson('/api/identity/login', [
            'email' => 'eq-client@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])->assertOk()->json('token');

        $this->withToken($clientToken)->getJson('/api/equipments')
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->withToken($clientToken)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'X',
            'brand' => 'Y',
            'type' => 'Z',
        ])->assertForbidden();
    }

    public function test_master_lists_equipment_without_clients(): void
    {
        $managerToken = $this->tokenAsManager('eq-master-mgr@example.com');
        $clientId = $this->createClient($managerToken, 'eq-master-client@example.com', 'Клиент EQ');
        $this->withToken($managerToken)->postJson('/api/actors/masters', [
            'email' => 'eq-master@example.com',
            'password' => 'password123',
            'name' => 'Мастер',
        ])->assertCreated();

        $this->withToken($managerToken)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'Фрезер',
            'brand' => 'Strong',
            'type' => 'Аппарат',
            'modules' => [
                ['name' => 'Блок', 'serial_number' => 'BLK-9'],
            ],
        ])->assertCreated();

        $masterToken = $this->postJson('/api/identity/login', [
            'email' => 'eq-master@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'masters',
        ])->assertOk()->json('token');

        $list = $this->withToken($masterToken)->getJson('/api/equipments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Фрезер')
            ->assertJsonPath('data.0.brand', 'Strong')
            ->assertJsonMissingPath('data.0.client_id')
            ->assertJsonMissingPath('data.0.client_name')
            ->json('data');

        $this->assertArrayNotHasKey('client_id', $list[0]);
        $this->assertArrayNotHasKey('client_name', $list[0]);

        $this->withToken($masterToken)->getJson('/api/equipments?q=BLK-9')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Фрезер');

        $this->withToken($masterToken)->getJson('/api/equipments?q=неттакого')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->withToken($masterToken)->postJson('/api/equipments', [
            'client_id' => $clientId,
            'name' => 'X',
            'brand' => 'Y',
            'type' => 'Z',
        ])->assertForbidden();
    }

    public function test_unknown_client_rejected(): void
    {
        $token = $this->tokenAsManager('eq-bad-client@example.com');

        $this->withToken($token)->postJson('/api/equipments', [
            'client_id' => 999999,
            'name' => 'X',
            'brand' => 'Y',
            'type' => 'Z',
        ])->assertStatus(422);
    }

    private function createClient(string $managerToken, string $email, string $name): int
    {
        return (int) $this->withToken($managerToken)->postJson('/api/actors/clients', [
            'email' => $email,
            'password' => 'password123',
            'name' => $name,
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
