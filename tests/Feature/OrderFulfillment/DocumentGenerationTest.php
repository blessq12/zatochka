<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\GenerateDocumentCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\GenerateDocumentHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Application\OrderFulfillment\Query\GetPosDashboardQuery;
use App\Application\OrderFulfillment\QueryHandler\GetPosDashboardQueryHandler;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_квитанция_о_приёме_генерирует_pdf(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $document = app(GenerateDocumentHandler::class)->handle(new GenerateDocumentCommand(
            orderId: $order->id(),
            type: DocumentType::Receipt,
            managerName: 'Менеджер',
        ));

        $this->assertStringStartsWith('%PDF', $document->content);
        $this->assertStringContainsString('receipt_', $document->filename);
    }

    public function test_акт_выдачи_только_для_ready(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();

        $this->expectException(OrderPolicyViolation::class);

        app(GenerateDocumentHandler::class)->handle(new GenerateDocumentCommand(
            orderId: $orderId,
            type: DocumentType::HandoverAct,
        ));
    }

    public function test_акт_выдачи_генерирует_pdf_с_работами(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
            problemDescription: 'Не включается',
        ));

        $orderId = $order->id();

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Замена кнопки питания',
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($orderId, $master->id));

        $document = app(GenerateDocumentHandler::class)->handle(new GenerateDocumentCommand(
            orderId: $orderId,
            type: DocumentType::HandoverAct,
            managerName: 'Менеджер',
        ));

        $this->assertStringStartsWith('%PDF', $document->content);
        $this->assertStringContainsString('handover_act_', $document->filename);
    }

    public function test_pos_dashboard_содержит_счётчики_и_среднее_время(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));

        $dashboard = app(GetPosDashboardQueryHandler::class)->handle(new GetPosDashboardQuery($master->id));

        $this->assertArrayHasKey('counts', $dashboard);
        $this->assertArrayHasKey('avg_work_duration_seconds', $dashboard);
        $this->assertIsInt($dashboard['avg_work_duration_seconds']);
    }

    public function test_pos_карточка_заказа_с_оборудованием_и_мастером(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: 'Аппарат',
            serialNumbers: ['SN-1'],
            brand: 'Strong',
        ));

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(LinkEquipmentToOrderHandler::class)->handle(new LinkEquipmentToOrderCommand($orderId, $equipment->id()));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Диагностика',
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($orderId, $master->id));

        $login = $this->postJson('/api/pos/login', [
            'email' => IdentitySeeder::MASTER_EMAIL,
            'password' => IdentitySeeder::DEMO_PASSWORD,
        ]);

        $this->getJson("/api/pos/orders/{$orderId}", [
            'Authorization' => 'Bearer '.$login->json('token'),
        ])
            ->assertOk()
            ->assertJsonPath('data.master.name', 'Демо Мастер')
            ->assertJsonPath('data.equipment.brand', 'Strong');
    }
}
