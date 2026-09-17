<?php

namespace Tests\Feature\Crm;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Infrastructure\Crm\Eloquent\ClientModel;
use App\Infrastructure\Crm\Eloquent\ManagerModel;
use App\Infrastructure\Crm\Eloquent\MasterModel;
use App\Infrastructure\Crm\Eloquent\ProfileAdditionalModel;
use App\Infrastructure\Identity\Eloquent\IdentityModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ActorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_client_creates_profile_additional(): void
    {
        $token = $this->tokenAsManager('owner@example.com');

        $response = $this->withToken($token)->postJson('/api/actors/clients', [
            'email' => 'client@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['id']);

        $client = ClientModel::query()->findOrFail($response->json('id'));

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('managers', 1);
        $this->assertDatabaseCount('profile_additionals', 2);
        $this->assertNotNull($client->profile_additional_id);
        $this->assertDatabaseHas('identities', ['email' => 'client@example.com']);
    }

    public function test_get_update_delete_client_flow(): void
    {
        $token = $this->tokenAsManager('manager@example.com');

        $created = $this->withToken($token)->postJson('/api/actors/clients', [
            'email' => 'client@example.com',
            'password' => 'password123',
        ])->assertCreated();

        $id = $created->json('id');
        $profileId = (int) ClientModel::query()->findOrFail($id)->profile_additional_id;

        $this->withToken($token)->getJson("/api/actors/clients/{$id}")
            ->assertOk()
            ->assertJson([
                'id' => $id,
                'profile_additional_id' => $profileId,
            ]);

        $this->withToken($token)->patchJson("/api/actors/clients/{$id}", [])
            ->assertOk()
            ->assertJson([
                'id' => $id,
                'profile_additional_id' => $profileId,
            ]);

        $this->withToken($token)->deleteJson("/api/actors/clients/{$id}")
            ->assertNoContent();

        $this->assertSoftDeleted('clients', ['id' => $id]);
        $this->assertSoftDeleted('profile_additionals', ['id' => $profileId]);
        $this->assertDatabaseMissing('identities', ['email' => 'client@example.com']);
        $this->assertDatabaseMissing('identity_actor_links', [
            'actor_type' => 'clients',
            'actor_id' => $id,
        ]);

        $this->withToken($token)->getJson("/api/actors/clients/{$id}")
            ->assertNotFound();

        $this->postJson('/api/identity/login', [
            'email' => 'client@example.com',
            'password' => 'password123',
        ])->assertStatus(422);
    }

    public function test_create_manager_and_master_smoke(): void
    {
        $token = $this->tokenAsManager('bootstrap@example.com');

        $this->withToken($token)->postJson('/api/actors/managers', [
            'email' => 'manager2@example.com',
            'password' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonStructure(['id']);

        $this->withToken($token)->postJson('/api/actors/masters', [
            'email' => 'master@example.com',
            'password' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonStructure(['id']);

        $this->assertDatabaseCount('managers', 2);
        $this->assertDatabaseCount('masters', 1);
        $this->assertInstanceOf(ManagerModel::class, ManagerModel::query()->first());
        $this->assertInstanceOf(MasterModel::class, MasterModel::query()->first());
        $this->assertGreaterThanOrEqual(3, ProfileAdditionalModel::query()->count());
        $this->assertInstanceOf(IdentityModel::class, IdentityModel::query()->where('email', 'manager2@example.com')->first());
    }

    public function test_unknown_actor_type_returns_422(): void
    {
        $token = $this->tokenAsManager('owner@example.com');

        $this->withToken($token)->postJson('/api/actors/robots', [
            'email' => 'robot@example.com',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJson(['message' => 'Unknown actor type.']);
    }

    public function test_actors_require_authentication(): void
    {
        $this->postJson('/api/actors/clients', [
            'email' => 'client@example.com',
            'password' => 'password123',
        ])->assertUnauthorized();
    }

    public function test_client_cannot_provision_actors(): void
    {
        $clientToken = $this->postJson('/api/identity/register', [
            'actor_type' => 'clients',
            'email' => 'only-client@example.com',
            'password' => 'password123',
        ])->assertCreated()->json('token');

        $this->withToken($clientToken)->postJson('/api/actors/masters', [
            'email' => 'master@example.com',
            'password' => 'password123',
        ])->assertForbidden();
    }

    private function tokenAsManager(string $email): string
    {
        $registered = $this->app->make(RegisterActorWithIdentityHandler::class)->handle(
            'managers',
            $email,
            'password123',
            issueToken: true,
        );

        return (string) $registered->token;
    }
}
