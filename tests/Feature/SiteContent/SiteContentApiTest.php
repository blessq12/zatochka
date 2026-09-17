<?php

namespace Tests\Feature\SiteContent;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_read_and_update_site_content(): void
    {
        $this->seed(\Database\Seeders\SiteContentSeeder::class);
        $token = $this->tokenAsManager('site-mgr@example.com');

        $this->withToken($token)->getJson('/api/site-content')
            ->assertOk()
            ->assertJsonPath('raw.company.name', 'Заточка.ТСК')
            ->assertJsonStructure(['raw', 'presented', 'legal']);

        $this->withToken($token)->putJson('/api/site-content/company', [
            'name' => 'Заточка.ТСК Updated',
            'tagline' => 'Новый слоган',
            'owner_name' => 'ИП Тест',
            'inn' => '123',
            'ogrn' => '456',
            'legal_address' => 'Адрес 1',
            'actual_address' => 'Адрес 2',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Заточка.ТСК Updated');

        $this->withToken($token)->putJson('/api/site-content/faq', [
            'items' => [
                [
                    'question' => 'Вопрос?',
                    'answer_lines' => ["Строка 1", "Строка 2"],
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.items.0.question', 'Вопрос?');

        $this->withToken($token)->putJson('/api/site-content/legal', [
            'slug' => 'privacy-policy',
            'type' => 'privacy_policy',
            'title' => 'Политика v2',
            'body_html' => '<p>Обновлено</p>',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Политика v2');
    }

    public function test_client_cannot_manage_site_content(): void
    {
        $clientToken = $this->postJson('/api/identity/register', [
            'actor_type' => 'clients',
            'email' => 'site-client@example.com',
            'password' => 'password123',
        ])->assertCreated()->json('token');

        $this->withToken($clientToken)->getJson('/api/site-content')->assertForbidden();
    }

    private function tokenAsManager(string $email): string
    {
        $registered = $this->app->make(RegisterActorWithIdentityHandler::class)->handle(
            'managers',
            $email,
            'password123',
            issueToken: true,
            name: 'Site Manager',
        );

        return (string) $registered->token;
    }
}
