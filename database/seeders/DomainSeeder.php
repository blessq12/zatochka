<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enum\PriceType;
use App\Infrastructure\Catalog\Persistence\Eloquent\BranchModel;
use App\Infrastructure\Catalog\Persistence\Eloquent\PriceBlockModel;
use App\Infrastructure\Catalog\Persistence\Eloquent\PriceItemModel;
use App\Infrastructure\Catalog\Persistence\Eloquent\SiteSettingModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
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

        UserModel::query()->firstOrCreate(
            ['email' => 'manager@zatochka.local'],
            [
                'name' => 'Демо',
                'surname' => 'Менеджер',
                'phone' => '+79000000002',
                'password' => Hash::make('password'),
            ],
        );

        $this->seedPrices();
        $this->seedSiteSettings($branch);
        $this->seedWarehouse();
    }

    private function seedWarehouse(): void
    {
        \App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel::query()->firstOrCreate(
            ['sku' => 'DEMO-001'],
            [
                'name' => 'Демо-запчасть',
                'category_name' => 'Расходники',
                'quantity' => 10,
                'unit' => 'шт',
                'price' => 250,
            ],
        );
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
                'contact_person' => 'МАКСИМ',
                'phone' => '+7 983 233 5907',
                'phone_tel' => '+79832335907',
                'email' => 'zatochka.tsk@yandex.ru',
                'address' => [
                    'main' => 'Пр. Ленина, 169 / пер. Карповский, 12',
                    'details' => [
                        'Остановка общественного транспорта «Центральный рынок»',
                        'Вход со стороны Ленина, ориентир магазин «Тайга»',
                        'в него не заходим идем направо до конца здания увидите нашу вывеску',
                    ],
                ],
                'social' => [
                    'email' => 'zatochka.tsk@yandex.ru',
                    'links' => [
                        ['name' => 'Telegram', 'icon' => 'telegram', 'url' => 'https://t.me/zatochka_tsk'],
                        ['name' => 'VKontakte', 'icon' => 'vk', 'url' => 'https://vk.com/zatochka_tsk'],
                        ['name' => 'WhatsApp', 'icon' => 'whatsapp', 'url' => 'https://wa.me/79832335907'],
                        ['name' => 'Instagram', 'icon' => 'instagram', 'url' => 'https://instagram.com/zatochka_tsk'],
                    ],
                ],
            ],
            'schedule' => [
                'days' => [
                    [
                        'id' => 1,
                        'name' => 'ПОНЕДЕЛЬНИК',
                        'is_day_off' => false,
                        'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                        'delivery' => '13:00 – 17:00 ДОСТАВКА',
                    ],
                    [
                        'id' => 2,
                        'name' => 'ВТОРНИК',
                        'is_day_off' => false,
                        'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                        'delivery' => '13:00 – 17:00 ДОСТАВКА',
                    ],
                    [
                        'id' => 3,
                        'name' => 'СРЕДА',
                        'is_day_off' => false,
                        'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                        'delivery' => '13:00 – 17:00 ДОСТАВКА',
                    ],
                    [
                        'id' => 4,
                        'name' => 'ЧЕТВЕРГ',
                        'is_day_off' => true,
                        'day_off_text' => 'ВСЕГДА ВЫХОДНОЙ',
                    ],
                    [
                        'id' => 5,
                        'name' => 'ПЯТНИЦА',
                        'is_day_off' => false,
                        'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                        'delivery' => '13:00 – 17:00 ДОСТАВКА',
                    ],
                    [
                        'id' => 6,
                        'name' => 'СУББОТА',
                        'is_day_off' => false,
                        'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                        'delivery' => '13:00 – 17:00 ДОСТАВКА',
                    ],
                    [
                        'id' => 7,
                        'name' => 'ВОСКРЕСЕНЬЕ',
                        'is_day_off' => true,
                        'day_off_text' => 'ВСЕГДА ВЫХОДНОЙ',
                    ],
                ],
            ],
            'delivery_info' => [
                'free_conditions' => [
                    'От 6 маникюрных инструментов',
                    'От 3 парикмахерских/грумерских/барберских инструментов',
                    'Любой аппарат в ремонт',
                ],
                'advantages' => [
                    [
                        'title' => 'Безопасная упаковка',
                        'description' => 'Используем специальную упаковку для защиты ваших инструментов',
                    ],
                    [
                        'title' => 'Гарантия качества',
                        'description' => 'Несем ответственность за сохранность ваших инструментов',
                    ],
                    [
                        'title' => 'Курьер забирает заказ',
                        'description' => 'с 13:00 до 17:00 часов в дни работы мастерской (пн, вт, ср, пт, сб) чт и вс всегда выходной.',
                    ],
                ],
            ],
            'company' => [
                'name' => 'ЗАТОЧКА.ТСК',
                'owner_name' => 'ИП Митькин Максим Игоревич',
                'inn' => '701744164429',
                'ogrn' => '323700000001333',
                'legal_address' => '634033, Томская обл., г. Томск, ул. Короленко, д. 17, кв. 12',
                'actual_address' => 'пер. Карповский, 12 / пр. Ленина, 169',
            ],
            'faq' => [
                'items' => [
                    [
                        'id' => 1,
                        'question' => 'Как долго делается заточка?',
                        'answer_lines' => [
                            'Стандартный срок заточки зависит от загруженности мастерской и количества инструмента.',
                            'Обычно работы выполняются в срок от 1 до 3 рабочих дней.',
                        ],
                    ],
                    [
                        'id' => 2,
                        'question' => 'Как долго ремонтируется аппарат?',
                        'answer_lines' => [
                            'Стандартный срок ремонта аппарата — 2 рабочих дня мастерской + время на доставку.',
                            'В случае сложного ремонта и отсутствия запчастей сроки дополнительно согласовываются.',
                        ],
                    ],
                    [
                        'id' => 3,
                        'question' => 'Есть гарантия на заточку?',
                        'answer_lines' => [
                            'Да, мы предоставляем гарантию при соблюдении рекомендаций по использованию инструмента.',
                        ],
                    ],
                    [
                        'id' => 4,
                        'question' => 'Есть гарантия на ремонт?',
                        'answer_lines' => [
                            'На все виды ремонта действует гарантия 14 дней с момента выдачи инструмента.',
                        ],
                    ],
                    [
                        'id' => 5,
                        'question' => 'Как происходит доставка?',
                        'answer_lines' => [
                            'Курьер забирает и привозит инструменты по согласованному адресу и времени.',
                            'Доставка доступна по условиям.',
                        ],
                    ],
                    [
                        'id' => 6,
                        'question' => 'Какие способы оплаты?',
                        'answer_lines' => [
                            'Вы можете оплатить услуги наличными или по безналичному расчету (перевод, счет).',
                        ],
                    ],
                ],
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
