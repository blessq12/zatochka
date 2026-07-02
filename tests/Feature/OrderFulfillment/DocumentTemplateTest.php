<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\GenerateDocumentCommand;
use App\Application\OrderFulfillment\Command\PreviewDocumentTemplateCommand;
use App\Application\OrderFulfillment\Command\UpdateDocumentTemplateCommand;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\GenerateDocumentHandler;
use App\Application\OrderFulfillment\CommandHandler\PreviewDocumentTemplateHandler;
use App\Application\OrderFulfillment\CommandHandler\UpdateDocumentTemplateHandler;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\OrderFulfillment\Document\DefaultDocumentTemplateBodies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_сохранение_шаблона_и_генерация_с_кастомным_телом(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $customBody = '<div class="document-part client-part"><p>Кастом {{order.number}} {{client.name}}</p></div>';

        app(UpdateDocumentTemplateHandler::class)->handle(new UpdateDocumentTemplateCommand(
            type: DocumentType::Receipt,
            body: $customBody,
            userId: null,
        ));

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Иван Тестов', 'phone' => '+79001112233']),
        ));

        $document = app(GenerateDocumentHandler::class)->handle(new GenerateDocumentCommand(
            orderId: $order->id(),
            type: DocumentType::Receipt,
            managerName: 'Менеджер',
        ));

        $this->assertStringStartsWith('%PDF', $document->content);
    }

    public function test_предпросмотр_шаблона_генерирует_pdf(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $document = app(PreviewDocumentTemplateHandler::class)->handle(new PreviewDocumentTemplateCommand(
            type: DocumentType::Receipt,
            body: DefaultDocumentTemplateBodies::forType(DocumentType::Receipt),
            orderId: $order->id(),
            managerName: 'Менеджер',
        ));

        $this->assertStringStartsWith('%PDF', $document->content);
    }
}
