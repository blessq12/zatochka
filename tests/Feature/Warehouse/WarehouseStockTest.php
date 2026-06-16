<?php

namespace Tests\Feature\Warehouse;

use App\Application\Warehouse\Command\ReceiveStockCommand;
use App\Application\Warehouse\Command\WriteOffStockCommand;
use App\Application\Warehouse\CommandHandler\ReceiveStockHandler;
use App\Application\Warehouse\CommandHandler\WriteOffStockHandler;
use App\Domain\Warehouse\Exception\WarehousePolicyViolation;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WarehouseStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_приход_и_списание_меняют_остаток(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $item = WarehouseItemModel::query()->where('sku', 'DEMO-001')->firstOrFail();

        app(ReceiveStockHandler::class)->handle(new ReceiveStockCommand(
            warehouseItemId: $item->id,
            quantity: '5.000',
        ));

        $item->refresh();
        $this->assertSame('15.000', number_format((float) $item->quantity, 3, '.', ''));

        app(WriteOffStockHandler::class)->handle(new WriteOffStockCommand(
            warehouseItemId: $item->id,
            quantity: '3.000',
        ));

        $item->refresh();
        $this->assertSame('12.000', number_format((float) $item->quantity, 3, '.', ''));
    }

    public function test_списание_больше_остатка_бросает_исключение(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $item = WarehouseItemModel::query()->where('sku', 'DEMO-001')->firstOrFail();

        $this->expectException(WarehousePolicyViolation::class);

        app(WriteOffStockHandler::class)->handle(new WriteOffStockCommand(
            warehouseItemId: $item->id,
            quantity: '999.000',
        ));
    }
}
