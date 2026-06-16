<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\RecalculateOrderPriceCommand;
use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\RecalculateOrderPriceHandler;
use App\Application\OrderFulfillment\CommandHandler\SetWorkPricesHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
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

        $master = UserModel::query()->where('email', 'master@zatochka.local')->firstOrFail();
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

        app(SetWorkPricesHandler::class)->handle(new SetWorkPricesCommand(
            orderId: $orderId,
            pricesBySortOrder: [$sortOrder => '1000.00'],
        ));

        app(AddMaterialToOrderHandler::class)->handle(new AddMaterialToOrderCommand(
            orderId: $orderId,
            warehouseItemId: $warehouseItem->id,
            quantity: '2.000',
        ));

        $order = app(RecalculateOrderPriceHandler::class)->handle(new RecalculateOrderPriceCommand($orderId));

        // 1000 + 2 × 250
        $this->assertSame('1500.00', $order->price());
    }
}
