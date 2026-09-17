<?php

namespace Tests\Feature\Order;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Domain\Order\Enum\DocumentType;
use App\Infrastructure\Order\Document\DefaultDocumentTemplateBodies;
use App\Infrastructure\Order\Eloquent\DocumentTemplateModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderDocumentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_generates_receipt_pdf_without_storing_it(): void
    {
        $this->seedTemplates();

        $token = $this->tokenAsManager('docs-mgr@example.com');
        $clientId = $this->createClient($token, 'docs-client@example.com');

        $orderId = $this->withToken($token)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '500.00',
            'needs_delivery' => false,
            'items' => [
                [
                    'kind' => 'sharpening',
                    'title' => 'Ножницы',
                    'quantity' => 2,
                ],
            ],
        ])->assertCreated()->json('id');

        $response = $this->withToken($token)->get("/api/orders/{$orderId}/documents/receipt");

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertDatabaseCount('document_templates', 2);
    }

    public function test_handover_act_requires_ready_or_issued(): void
    {
        $this->seedTemplates();

        $token = $this->tokenAsManager('docs-mgr2@example.com');
        $clientId = $this->createClient($token, 'docs-client2@example.com');

        $orderId = $this->withToken($token)->postJson('/api/orders', [
            'client_id' => $clientId,
            'billing_type' => 'paid',
            'urgency' => 'normal',
            'estimated_cost' => '500.00',
            'needs_delivery' => false,
            'items' => [
                [
                    'kind' => 'sharpening',
                    'title' => 'Кусачки',
                    'quantity' => 1,
                ],
            ],
        ])->assertCreated()->json('id');

        $this->withToken($token)
            ->getJson("/api/orders/{$orderId}/documents/handover_act")
            ->assertStatus(422);
    }

    public function test_manager_updates_and_lists_templates(): void
    {
        $this->seedTemplates();
        $token = $this->tokenAsManager('docs-tpl@example.com');

        $this->withToken($token)
            ->getJson('/api/order-document-templates')
            ->assertOk()
            ->assertJsonStructure(['templates', 'variables', 'loops']);

        $this->withToken($token)
            ->putJson('/api/order-document-templates/receipt', [
                'body' => '<div>{{order.number}}</div>',
            ])
            ->assertOk()
            ->assertJson([
                'type' => 'receipt',
                'body' => '<div>{{order.number}}</div>',
            ]);
    }

    private function seedTemplates(): void
    {
        foreach (DocumentType::cases() as $type) {
            DocumentTemplateModel::query()->updateOrCreate(
                ['type' => $type->value],
                ['body' => DefaultDocumentTemplateBodies::forType($type)],
            );
        }
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
