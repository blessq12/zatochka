<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\PriceType;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\PriceBlockModel;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\PriceItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\SiteSettingModel;
use App\Infrastructure\Persistence\Eloquent\Models\Identity\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $branch = BranchModel::query()->firstOrCreate(
            ['name' => 'Центральный филиал'],
            [
                'address' => 'г. Томск',
                'phone' => null,
                'is_active' => true,
            ],
        );

        UserModel::query()->firstOrCreate(
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
        $sharpeningBlock = PriceBlockModel::query()->firstOrCreate(
            ['type' => PriceType::Sharpening, 'title' => 'Заточка инструмента'],
            ['sort_order' => 1],
        );

        PriceItemModel::query()->firstOrCreate(
            ['price_block_id' => $sharpeningBlock->id, 'name' => 'Маникюрный инструмент (1 шт.)'],
            ['price' => 300, 'sort_order' => 1],
        );

        $repairBlock = PriceBlockModel::query()->firstOrCreate(
            ['type' => PriceType::Repair, 'title' => 'Ремонт аппаратов'],
            ['sort_order' => 2],
        );

        PriceItemModel::query()->firstOrCreate(
            ['price_block_id' => $repairBlock->id, 'name' => 'Диагностика'],
            ['price' => 500, 'sort_order' => 1],
        );
    }

    private function seedSiteSettings(BranchModel $branch): void
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
            SiteSettingModel::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }
    }
}
