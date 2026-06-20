<?php

namespace Database\Seeders;

use App\Infrastructure\Company\Persistence\Eloquent\BranchModel;
use App\Infrastructure\Company\Persistence\Eloquent\CompanySettingModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $branch = [
            'name' => 'ЗАТОЧКА.ТСК',
            'address' => 'г. Томск, пр. Ленина, 169 / пер. Карповский, 12',
            'phone' => '+7 983 233 5907',
            'is_active' => true,
        ];

        $record = BranchModel::query()->updateOrCreate(
            ['name' => $branch['name']],
            $branch,
        );

        DB::table('orders')
            ->where('branch_id', '!=', $record->id)
            ->update(['branch_id' => $record->id]);

        BranchModel::query()
            ->where('id', '!=', $record->id)
            ->delete();

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
            'company' => [
                'name' => 'ЗАТОЧКА.ТСК',
                'owner_name' => 'ИП Митькин Максим Игоревич',
                'inn' => '701744164429',
                'ogrn' => '323700000001333',
                'legal_address' => '634033, Томская обл., г. Томск, ул. Короленко, д. 17, кв. 12',
                'actual_address' => 'пер. Карповский, 12 / пр. Ленина, 169',
            ],
        ];

        foreach ($settings as $key => $value) {
            CompanySettingModel::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }
    }
}
