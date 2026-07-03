<?php

namespace Database\Seeders;

use App\Domain\Pricing\Enum\PriceType;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceBlockModel;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceItemModel;
use Illuminate\Database\Seeder;

final class PricingSeeder extends Seeder
{
    public function run(): void
    {
        $sharpeningBlock = PriceBlockModel::query()->updateOrCreate(
            ['type' => PriceType::Sharpening, 'title' => 'Заточка инструмента'],
            ['sort_order' => 1],
        );

        $sharpeningItems = [
            ['name' => 'Маникюрный инструмент (1 шт.)', 'price' => 300, 'sort_order' => 1],
            ['name' => 'Парикмахерские ножницы (1 пара)', 'price' => 400, 'sort_order' => 2],
            ['name' => 'Грумерские / барберские ножницы (1 пара)', 'price' => 450, 'sort_order' => 3],
            ['name' => 'Топор / секатор (1 шт.)', 'price' => 350, 'sort_order' => 4],
            ['name' => 'Нож кухонный (1 шт.)', 'price' => 300, 'sort_order' => 5],
        ];

        foreach ($sharpeningItems as $item) {
            PriceItemModel::query()->updateOrCreate(
                ['price_block_id' => $sharpeningBlock->id, 'name' => $item['name']],
                ['price' => $item['price'], 'sort_order' => $item['sort_order']],
            );
        }

        $repairBlock = PriceBlockModel::query()->updateOrCreate(
            ['type' => PriceType::Repair, 'title' => 'Ремонт аппаратов'],
            ['sort_order' => 2],
        );

        $repairItems = [
            ['name' => 'Диагностика', 'price' => 500, 'sort_order' => 1],
            ['name' => 'Замена подшипника', 'price' => 1200, 'sort_order' => 2],
            ['name' => 'Замена приводного ремня', 'price' => 900, 'sort_order' => 3],
            ['name' => 'Чистка и смазка', 'price' => 700, 'sort_order' => 4],
        ];

        foreach ($repairItems as $item) {
            PriceItemModel::query()->updateOrCreate(
                ['price_block_id' => $repairBlock->id, 'name' => $item['name']],
                ['price' => $item['price'], 'sort_order' => $item['sort_order']],
            );
        }
    }
}
