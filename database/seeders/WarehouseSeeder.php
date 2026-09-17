<?php

namespace Database\Seeders;

use App\Application\Warehouse\Command\CreateStockItemHandler;
use App\Infrastructure\Warehouse\Eloquent\StockItemModel;
use Illuminate\Database\Seeder;

final class WarehouseSeeder extends Seeder
{
    public function run(CreateStockItemHandler $create): void
    {
        if (StockItemModel::query()->exists()) {
            $this->command?->info('Warehouse catalog already seeded.');

            return;
        }

        $items = [
            ['category' => 'spare_part', 'name' => 'Подшипник 6202', 'unit' => 'шт', 'qty_on_hand' => '25'],
            ['category' => 'spare_part', 'name' => 'Ремень приводной', 'unit' => 'шт', 'qty_on_hand' => '12'],
            ['category' => 'spare_part', 'name' => 'Щётки угольные', 'unit' => 'пар', 'qty_on_hand' => '40'],
            ['category' => 'consumable', 'name' => 'Абразивная паста', 'unit' => 'кг', 'qty_on_hand' => '8.5'],
            ['category' => 'consumable', 'name' => 'Шлифовальная лента', 'unit' => 'м', 'qty_on_hand' => '50'],
            ['category' => 'consumable', 'name' => 'Масло индустриальное', 'unit' => 'л', 'qty_on_hand' => '15'],
        ];

        foreach ($items as $row) {
            $create->handle(
                $row['category'],
                $row['name'],
                $row['unit'],
                $row['qty_on_hand'],
            );
        }

        $this->command?->info('Warehouse catalog seeded: '.count($items).' items.');
    }
}
