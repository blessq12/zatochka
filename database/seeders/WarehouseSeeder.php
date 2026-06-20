<?php

namespace Database\Seeders;

use App\Application\Warehouse\Command\ReceiveStockCommand;
use App\Application\Warehouse\CommandHandler\ReceiveStockHandler;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Database\Seeder;

final class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'sku' => 'DEMO-001',
                'name' => 'Подшипник 608ZZ',
                'category_name' => 'Запчасти',
                'quantity' => 0,
                'unit' => 'шт',
                'price' => 250,
                'receive_qty' => '10',
            ],
            [
                'sku' => 'PART-BELT-V',
                'name' => 'Приводной ремень V-belt',
                'category_name' => 'Запчасти',
                'quantity' => 0,
                'unit' => 'шт',
                'price' => 180,
                'receive_qty' => '8',
            ],
            [
                'sku' => 'CONSUMABLE-OIL',
                'name' => 'Масло для смазки подшипников',
                'category_name' => 'Расходники',
                'quantity' => 0,
                'unit' => 'мл',
                'price' => 5,
                'receive_qty' => '500',
            ],
            [
                'sku' => 'CONSUMABLE-ABRASIVE',
                'name' => 'Абразивная лента для заточки',
                'category_name' => 'Расходники',
                'quantity' => 0,
                'unit' => 'м',
                'price' => 120,
                'receive_qty' => '20',
            ],
        ];

        $manager = UserModel::query()
            ->where('email', IdentitySeeder::MANAGER_EMAIL)
            ->firstOrFail();

        $receiveStock = app(ReceiveStockHandler::class);

        foreach ($items as $data) {
            $receiveQty = $data['receive_qty'];
            unset($data['receive_qty']);

            $model = WarehouseItemModel::query()->updateOrCreate(
                ['sku' => $data['sku']],
                $data,
            );

            if ((float) $model->quantity > 0) {
                continue;
            }

            $receiveStock->handle(new ReceiveStockCommand(
                warehouseItemId: $model->id,
                quantity: $receiveQty,
                comment: 'Начальный остаток (сидер)',
                userId: $manager->id,
            ));
        }
    }
}
