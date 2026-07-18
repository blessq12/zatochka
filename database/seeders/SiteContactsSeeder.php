<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SiteContactsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_contacts')->updateOrInsert(
            ['id' => 1],
            [
                'contact_person' => 'Митькин Максим Игоревич',
                'phone' => '+7 (983) 233-59-07',
                'phone_tel' => '+79832335907',
                'email' => 'zatochka.tsk@yandex.ru',
                'address_main' => 'Пр. Ленина, 169 / пер. Карповский, 12',
                'address_details' => json_encode([
                    'Остановка общественного транспорта «Центральный рынок»',
                    'Вход со стороны Ленина, ориентир магазин «Тайга»',
                    'в него не заходим идем направо до конца здания увидите нашу вывеску',
                ], JSON_UNESCAPED_UNICODE),
                'social_links' => json_encode([
                    ['name' => 'Telegram', 'url' => 'https://t.me/zatochka_tsk', 'icon' => null],
                    ['name' => 'ВКонтакте', 'url' => 'https://vk.com/zatochka_tsk', 'icon' => null],
                    ['name' => 'WhatsApp', 'url' => 'https://wa.me/79832335907', 'icon' => null],
                    ['name' => 'Instagram', 'url' => 'https://instagram.com/zatochka_tsk', 'icon' => null],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }
}
