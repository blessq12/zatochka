<?php

namespace Tests\Feature\Identity;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IdentityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_me_logout_flow(): void
    {
        $register = $this->postJson('/api/identity/register', [
            'actor_type' => 'clients',
            'email' => 'user@example.com',
            'password' => 'password123',
        ])->assertCreated()
            ->assertJsonStructure([
                'id',
                'email',
                'token',
                'actor' => ['type', 'id'],
            ])
            ->assertJsonMissingPath('actor.profile_additional_id');

        $this->assertSame('clients', $register->json('actor.type'));
        $this->assertDatabaseHas('identity_actor_links', [
            'identity_id' => $register->json('id'),
            'actor_type' => 'clients',
            'actor_id' => $register->json('actor.id'),
        ]);

        $token = $register->json('token');

        $this->withToken($token)->getJson('/api/identity/me')
            ->assertOk()
            ->assertJson([
                'id' => $register->json('id'),
                'email' => 'user@example.com',
                'actor' => [
                    'type' => 'clients',
                    'id' => $register->json('actor.id'),
                ],
            ])
            ->assertJsonMissing(['token']);

        $login = $this->postJson('/api/identity/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])->assertOk()
            ->assertJsonStructure(['id', 'email', 'token', 'actor']);

        $this->withToken($login->json('token'))->postJson('/api/identity/logout')
            ->assertNoContent();

        $this->withToken($login->json('token'))->getJson('/api/identity/me')
            ->assertUnauthorized();
    }

    public function test_login_rejects_wrong_expected_actor_type(): void
    {
        $this->app->make(RegisterActorWithIdentityHandler::class)->handle(
            'managers',
            'manager@example.com',
            'password123',
            issueToken: false,
        );

        $this->postJson('/api/identity/login', [
            'email' => 'manager@example.com',
            'password' => 'password123',
            'expected_actor_type' => 'clients',
        ])
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden for this actor type.']);
    }

    public function test_register_rejects_non_client_actor_type(): void
    {
        $this->postJson('/api/identity/register', [
            'actor_type' => 'managers',
            'email' => 'manager@example.com',
            'password' => 'password123',
        ])->assertStatus(422);
    }

    public function test_duplicate_email_returns_422(): void
    {
        $this->postJson('/api/identity/register', [
            'actor_type' => 'clients',
            'email' => 'user@example.com',
            'password' => 'password123',
        ])->assertCreated();

        $this->postJson('/api/identity/register', [
            'actor_type' => 'clients',
            'email' => 'user@example.com',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJson(['message' => 'Email already taken.']);
    }

    public function test_me_and_logout_require_authentication(): void
    {
        $this->getJson('/api/identity/me')->assertUnauthorized();
        $this->postJson('/api/identity/logout')->assertUnauthorized();
    }
}
