<?php

namespace Tests\Feature\Equipment;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\UpdateEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Application\Equipment\CommandHandler\UpdateEquipmentHandler;
use App\Application\Equipment\Query\GetEquipmentOrderHistoryQuery;
use App\Application\Equipment\Query\SearchEquipmentQuery;
use App\Application\Equipment\QueryHandler\GetEquipmentOrderHistoryQueryHandler;
use App\Application\Equipment\QueryHandler\SearchEquipmentQueryHandler;
use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Database\Seeders\DomainSeeder;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_регистрация_поиск_и_история_заказов(): void
    {
        $this->seed(DomainSeeder::class);

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: 'Аппарат Strong 9093',
            serialNumbers: [
                'ручка' => 'SN-12345',
            ],
            brand: 'Strong',
            model: '9093',
        ));

        $equipmentId = $equipment->id();
        $this->assertNotNull($equipmentId);

        $search = app(SearchEquipmentQueryHandler::class)->handle(new SearchEquipmentQuery('SN-12345'));
        $this->assertCount(1, $search['items']);
        $this->assertSame($equipmentId, $search['items'][0]->id());

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(LinkEquipmentToOrderHandler::class)->handle(new LinkEquipmentToOrderCommand(
            orderId: $orderId,
            equipmentId: $equipmentId,
        ));

        $history = app(GetEquipmentOrderHistoryQueryHandler::class)->handle(
            new GetEquipmentOrderHistoryQuery($equipmentId),
        );

        $this->assertCount(1, $history);
        $this->assertSame($orderId, $history[0]->id());
    }

    public function test_pos_поиск_оборудования(): void
    {
        $this->seed(DomainSeeder::class);

        app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: 'Демо аппарат',
            serialNumbers: ['корпус' => 'DEMO-SN'],
        ));

        $login = $this->postJson('/api/pos/login', [
            'email' => IdentitySeeder::MASTER_EMAIL,
            'password' => IdentitySeeder::DEMO_PASSWORD,
        ]);

        $token = $login->json('token');

        $this->getJson('/api/pos/equipment?query=DEMO-SN', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Демо аппарат');
    }

    public function test_pos_история_оборудования_содержит_работы(): void
    {
        $this->seed(DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: 'Аппарат Wahl',
            serialNumbers: ['корпус' => 'POS-HIST-SN'],
            brand: 'Wahl',
        ));

        $equipmentId = $equipment->id();
        $this->assertNotNull($equipmentId);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Иван', 'phone' => '+79001112233']),
            problemDescription: 'Не включается',
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(LinkEquipmentToOrderHandler::class)->handle(new LinkEquipmentToOrderCommand($orderId, $equipmentId));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Замена кнопки питания',
        ));

        $login = $this->postJson('/api/pos/login', [
            'email' => IdentitySeeder::MASTER_EMAIL,
            'password' => IdentitySeeder::DEMO_PASSWORD,
        ]);

        $this->getJson("/api/pos/equipment/{$equipmentId}/orders", [
            'Authorization' => 'Bearer '.$login->json('token'),
        ])
            ->assertOk()
            ->assertJsonPath('data.0.problem_description', 'Не включается')
            ->assertJsonPath('data.0.works.0.description', 'Замена кнопки питания')
            ->assertJsonPath('data.0.works_count', 1)
            ->assertJsonPath('data.0.master_name', 'Демо Мастер');
    }

    public function test_обновление_оборудования(): void
    {
        $this->seed(DomainSeeder::class);

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: 'Старое название',
            serialNumbers: ['ручка' => 'OLD-SN'],
            brand: 'OldBrand',
            model: 'OldModel',
        ));

        $equipmentId = $equipment->id();
        $this->assertNotNull($equipmentId);

        $updated = app(UpdateEquipmentHandler::class)->handle(new UpdateEquipmentCommand(
            equipmentId: $equipmentId,
            name: 'Новое название',
            serialNumbers: [
                'ручка' => 'NEW-SN-1',
                'блок питания' => 'NEW-SN-2',
            ],
            brand: 'NewBrand',
            model: 'NewModel',
        ));

        $this->assertSame('Новое название', $updated->name());
        $this->assertSame('NewBrand', $updated->brand());
        $this->assertSame('NewModel', $updated->model());
        $this->assertSame([
            'ручка' => 'NEW-SN-1',
            'блок питания' => 'NEW-SN-2',
        ], $updated->serialNumbers());

        $search = app(SearchEquipmentQueryHandler::class)->handle(new SearchEquipmentQuery('NEW-SN-2'));
        $this->assertCount(1, $search['items']);
        $this->assertSame($equipmentId, $search['items'][0]->id());
    }
}
