<?php

namespace Tests\Feature\Crm;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Infrastructure\Identity\Eloquent\IdentityActorLinkModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WalkInClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_creates_walk_in_client_without_identity(): void
    {
        $token = $this->tokenAsManager('walk-mgr@example.com');

        $created = $this->withToken($token)->postJson('/api/actors/clients/walk-in', [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
        ])->assertCreated()
            ->assertJson([
                'type' => 'clients',
                'name' => 'Иван Иванов',
                'phone' => '+79991234567',
                'email' => null,
            ])
            ->json();

        $this->assertDatabaseHas('clients', ['id' => $created['id']]);
        $this->assertSame(
            0,
            IdentityActorLinkModel::query()
                ->where('actor_type', 'clients')
                ->where('actor_id', $created['id'])
                ->count(),
        );
    }

    public function test_search_and_recent_clients(): void
    {
        $token = $this->tokenAsManager('search-mgr@example.com');

        $this->withToken($token)->postJson('/api/actors/clients/walk-in', [
            'name' => 'Анна Петрова',
            'phone' => '+79990001122',
        ])->assertCreated();

        $this->withToken($token)->postJson('/api/actors/clients/walk-in', [
            'name' => 'Борис Сидоров',
            'phone' => '+79993334455',
        ])->assertCreated();

        $this->withToken($token)->getJson('/api/actors/clients?'.http_build_query(['q' => 'Анна']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Анна Петрова');

        $this->withToken($token)->getJson('/api/actors/clients?'.http_build_query(['q' => 'анна']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Анна Петрова');

        $this->withToken($token)->getJson('/api/actors/clients?'.http_build_query(['q' => 'ПЕТРОВА']))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson('/api/actors/clients?'.http_build_query(['q' => '9990001122']))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson('/api/actors/clients?recent=1')
            ->assertOk();

        $this->assertGreaterThanOrEqual(
            2,
            count($this->withToken($token)->getJson('/api/actors/clients?recent=1')->json('data')),
        );
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
