<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Filament\Support\OrderManageActionSupport;
use Database\Seeders\IdentitySeeder;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_пересчёт_цены_работы_плюс_материалы(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();
        $warehouseItem = WarehouseItemModel::query()->where('sku', 'DEMO-001')->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));

        $order = app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Замена подшипника',
        ));

        $sortOrder = $order->works()[0]->sortOrder;

        $order = OrderManageActionSupport::setWorkPrices($orderId, [$sortOrder => '1000.00']);

        $this->assertSame('1000.00', $order->price());

        $order = OrderManageActionSupport::addMaterial($orderId, $warehouseItem->id, '2.000');

        // 1000 + 2 × 250
        $this->assertSame('1500.00', $order->price());
    }

    public function test_order_manage_action_support_пересчитывает_без_ручного_recalc(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();
        $warehouseItem = WarehouseItemModel::query()->where('sku', 'DEMO-001')->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));

        $order = app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Диагностика',
        ));

        $sortOrder = $order->works()[0]->sortOrder;

        OrderManageActionSupport::setWorkPrices($orderId, [$sortOrder => '500.00']);

        $reloaded = app(OrderRepositoryInterface::class)->findById($orderId);
        $this->assertNotNull($reloaded);
        $this->assertSame('500.00', $reloaded->price());

        OrderManageActionSupport::addMaterial($orderId, $warehouseItem->id, '1.000');

        $reloaded = app(OrderRepositoryInterface::class)->findById($orderId);
        $this->assertNotNull($reloaded);
        $this->assertSame('750.00', $reloaded->price());
    }

    public function test_заточка_цена_за_единицу_умножается_на_количество_инструментов(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
            tools: [
                new OrderTool(null, 'manicure', 3, 'Ножницы'),
            ],
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));

        $order = app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Заточка',
            toolType: 'manicure',
        ));

        $sortOrder = $order->works()[0]->sortOrder;

        // Менеджер вводит 200 ₽ за единицу, в заказе 3 инструмента
        $order = OrderManageActionSupport::setWorkPrices($orderId, [$sortOrder => '200.00']);

        $this->assertSame('600.00', $order->price());
        $this->assertSame('600.00', $order->works()[0]->price);
    }

    public function test_заточка_цена_за_единицу_умножается_на_количество_своего_типа(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
            tools: [
                new OrderTool(null, 'manicure', 3, 'Кусачки'),
                new OrderTool(null, 'hair', 4, 'Ножницы'),
            ],
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));

        $order = app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Заточка маникюрных',
            toolType: 'manicure',
        ));

        $order = app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Заточка парикмахерских',
            toolType: 'hair',
        ));

        $manicureSortOrder = $order->works()[0]->sortOrder;
        $hairSortOrder = $order->works()[1]->sortOrder;

        $order = OrderManageActionSupport::setWorkPrices($orderId, [
            $manicureSortOrder => '200.00',
            $hairSortOrder => '300.00',
        ]);

        $this->assertSame('1800.00', $order->price());
        $this->assertSame('600.00', $order->works()[0]->price);
        $this->assertSame('1200.00', $order->works()[1]->price);
    }
}
