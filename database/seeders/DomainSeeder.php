<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\PriceType;
use App\Domain\Catalog\Models\Branch;
use App\Domain\Catalog\Models\PriceBlock;
use App\Domain\Catalog\Models\PriceItem;
use App\Domain\Catalog\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::query()->firstOrCreate(
            ['name' => 'Центральный филиал'],
            [
                'address' => 'г. Томск',
                'phone' => null,
                'is_active' => true,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'master@zatochka.local'],
            [
                'name' => 'Демо',
                'surname' => 'Мастер',
                'phone' => '+79000000001',
                'password' => Hash::make('password'),
            ],
        );

        $this->seedPrices();
        $this->seedSiteSettings($branch);
    }

    private function seedPrices(): void
    {
        $sharpeningBlock = PriceBlock::query()->firstOrCreate(
            ['type' => PriceType::Sharpening, 'title' => 'Заточка инструмента'],
            ['sort_order' => 1],
        );

        PriceItem::query()->firstOrCreate(
            ['price_block_id' => $sharpeningBlock->id, 'name' => 'Маникюрный инструмент (1 шт.)'],
            ['price' => 300, 'sort_order' => 1],
        );

        $repairBlock = PriceBlock::query()->firstOrCreate(
            ['type' => PriceType::Repair, 'title' => 'Ремонт аппаратов'],
            ['sort_order' => 2],
        );

        PriceItem::query()->firstOrCreate(
            ['price_block_id' => $repairBlock->id, 'name' => 'Диагностика'],
            ['price' => 500, 'sort_order' => 1],
        );
    }

    private function seedSiteSettings(Branch $branch): void
    {
        $settings = [
            'contacts' => [
                'phone' => '+7 (3822) 00-00-00',
                'email' => 'info@zatochka.tsk',
                'address' => $branch->address,
            ],
            'schedule' => [
                'workshop' => 'Пн–Сб: 10:00–19:00',
                'delivery' => 'Пн–Сб: 13:00–17:00',
            ],
            'delivery_info' => [
                'free_from_tools' => 5,
                'window' => '13:00–17:00',
            ],
            'company' => [
                'name' => 'ЗАТОЧКА.ТСК',
                'legal_name' => null,
            ],
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }
    }
}
