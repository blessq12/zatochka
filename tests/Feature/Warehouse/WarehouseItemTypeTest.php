<?php

namespace Tests\Feature\Warehouse;

use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WarehouseItemTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_запчасть_нельзя_добавить_в_заказ_на_заточку(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $sparePart = WarehouseItemModel::query()->where('sku', 'DEMO-001')->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $this->expectException(OrderPolicyViolation::class);

        app(AddMaterialToOrderHandler::class)->handle(new AddMaterialToOrderCommand(
            orderId: $order->id(),
            warehouseItemId: $sparePart->id,
            quantity: '1.000',
        ));
    }

    public function test_запчасть_можно_добавить_в_заказ_на_ремонт(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $sparePart = WarehouseItemModel::query()->where('sku', 'DEMO-001')->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $updated = app(AddMaterialToOrderHandler::class)->handle(new AddMaterialToOrderCommand(
            orderId: $order->id(),
            warehouseItemId: $sparePart->id,
            quantity: '1.000',
        ));

        $this->assertCount(1, $updated->materials());
        $this->assertSame($sparePart->id, $updated->materials()[0]->warehouseItemId);
    }
}
