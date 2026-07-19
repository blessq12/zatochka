<?php

namespace Tests\Feature\Documents;

use App\Application\Documents\Command\GenerateOrderPdfCommand;
use App\Application\Documents\Command\GenerateOrderPdfHandler;
use App\Application\Documents\Command\UpdateLegalDocumentCommand;
use App\Application\Documents\Command\UpdateLegalDocumentHandler;
use App\Application\Documents\Port\CompanyDocumentReadPort;
use App\Domain\Documents\VO\DocumentType;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Documents\Model\LegalDocumentModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use App\Shared\Domain\DomainException;
use Database\Seeders\DocumentsSeeder;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DocumentsVerticalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteContentSeeder::class);
        $this->seed(DocumentsSeeder::class);
    }

    public function test_update_legal_document_persists_content(): void
    {
        app(UpdateLegalDocumentHandler::class)->handle(new UpdateLegalDocumentCommand(
            DocumentType::PrivacyPolicy,
            'Политика v2',
            '<p>Обновлённый текст политики</p>',
        ));

        $this->assertDatabaseHas('legal_documents', [
            'type' => DocumentType::PrivacyPolicy->value,
            'title' => 'Политика v2',
        ]);

        $body = (string) LegalDocumentModel::query()
            ->where('type', DocumentType::PrivacyPolicy->value)
            ->value('body_html');

        $this->assertStringContainsString('Обновлённый текст политики', $body);
    }

    public function test_public_api_returns_legal_document(): void
    {
        $response = $this->getJson('/api/documents/privacy-policy');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'privacy-policy')
            ->assertJsonPath('data.title', DocumentType::PrivacyPolicy->label())
            ->assertJsonStructure([
                'data' => ['type', 'slug', 'title', 'body_html', 'updated_at'],
            ]);
    }

    public function test_public_api_maps_legacy_terms_slug_to_user_agreement(): void
    {
        $response = $this->getJson('/api/documents/terms-of-service');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'user-agreement')
            ->assertJsonPath('data.type', DocumentType::UserAgreement->value);
    }

    public function test_generate_reception_receipt_returns_pdf_bytes(): void
    {
        $orderId = $this->createOrder(OrderStatus::Created);

        $result = app(GenerateOrderPdfHandler::class)->handle(
            new GenerateOrderPdfCommand($orderId, PdfTemplateKind::ReceptionReceipt),
        );

        $this->assertStringStartsWith('%PDF', $result->bytes);
        $this->assertStringContainsString('reception_receipt', $result->filename);
    }

    public function test_generate_reception_receipt_blocked_after_created(): void
    {
        $orderId = $this->createOrder(OrderStatus::ReceptionCompleted);

        $this->expectException(DomainException::class);

        app(GenerateOrderPdfHandler::class)->handle(
            new GenerateOrderPdfCommand($orderId, PdfTemplateKind::ReceptionReceipt),
        );
    }

    public function test_generate_issue_act_blocked_before_ready(): void
    {
        $orderId = $this->createOrder(OrderStatus::ReceptionCompleted);

        $this->expectException(DomainException::class);

        app(GenerateOrderPdfHandler::class)->handle(
            new GenerateOrderPdfCommand($orderId, PdfTemplateKind::IssueAct),
        );
    }

    public function test_generate_issue_act_allowed_when_ready(): void
    {
        $orderId = $this->createOrder(OrderStatus::Ready);

        $result = app(GenerateOrderPdfHandler::class)->handle(
            new GenerateOrderPdfCommand($orderId, PdfTemplateKind::IssueAct),
        );

        $this->assertStringStartsWith('%PDF', $result->bytes);
        $this->assertStringContainsString('issue_act', $result->filename);
    }

    public function test_generate_issue_act_allowed_when_issued(): void
    {
        $orderId = $this->createOrder(OrderStatus::Issued);

        $result = app(GenerateOrderPdfHandler::class)->handle(
            new GenerateOrderPdfCommand($orderId, PdfTemplateKind::IssueAct),
        );

        $this->assertStringStartsWith('%PDF', $result->bytes);
        $this->assertStringContainsString('issue_act', $result->filename);
    }

    public function test_company_document_logo_uses_embedded_png(): void
    {
        $placeholders = app(CompanyDocumentReadPort::class)->get()->placeholders;

        $this->assertArrayHasKey('logo', $placeholders);
        $this->assertStringContainsString('data:image/png;base64,', $placeholders['logo']);
        $this->assertStringContainsString('<img ', $placeholders['logo']);
    }

    public function test_print_page_requires_auth(): void
    {
        $orderId = $this->createOrder(OrderStatus::Created);

        $response = $this->get(route('documents.orders.print', [
            'orderId' => $orderId,
            'kind' => PdfTemplateKind::ReceptionReceipt->value,
        ]));

        $this->assertNotSame(200, $response->status());
    }

    public function test_print_pdf_returns_inline_pdf_for_authenticated_user(): void
    {
        $orderId = $this->createOrder(OrderStatus::Created);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('documents.orders.print.pdf', [
            'orderId' => $orderId,
            'kind' => PdfTemplateKind::ReceptionReceipt->value,
        ]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_print_page_renders_for_authenticated_user(): void
    {
        $orderId = $this->createOrder(OrderStatus::Created);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('documents.orders.print', [
            'orderId' => $orderId,
            'kind' => PdfTemplateKind::ReceptionReceipt->value,
        ]))
            ->assertOk()
            ->assertSee('Печать', false)
            ->assertSee(route('documents.orders.print.pdf', [
                'orderId' => $orderId,
                'kind' => PdfTemplateKind::ReceptionReceipt->value,
            ]), false);
    }

    private function createOrder(OrderStatus $status): string
    {
        $now = now();
        $clientId = 9001;

        DB::table('clients')->insert([
            'id' => $clientId,
            'phone' => '+7 (900) 000-00-01',
            'name' => 'Тест Клиент',
            'email' => null,
            'birth_date' => null,
            'delivery_address' => null,
            'password' => null,
            'bonus_account_id' => $clientId,
            'bonus_balance' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $orderId = 'ord-doc-'.substr($status->value, 0, 12);

        OrderModel::query()->create([
            'id' => $orderId,
            'number' => 'ORD-'.strtoupper(substr($status->value, 0, 8)),
            'client_id' => $clientId,
            'status' => $status->value,
            'service_type' => 'sharpening',
            'source' => 'website',
            'billing_type' => 'cash',
            'urgency' => 'standard',
            'delivery_required' => false,
            'defects' => null,
            'internal_notes' => null,
            'client_comment' => null,
            'manager_rework_comment' => null,
            'warranty_source_order_id' => null,
            'assigned_master_id' => null,
            'estimated_amount' => '0',
            'estimated_currency' => 'RUB',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $orderId;
    }
}
