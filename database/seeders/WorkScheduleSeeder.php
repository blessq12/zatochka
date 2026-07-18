<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_schedule_days')->delete();
        DB::table('site_schedule_days')->insert([
            [
                'id' => 1,
                'name' => 'ПОНЕДЕЛЬНИК',
                'is_day_off' => false,
                'day_off_text' => null,
                'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                'delivery' => '13:00 – 17:00 ДОСТАВКА',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'ВТОРНИК',
                'is_day_off' => false,
                'day_off_text' => null,
                'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                'delivery' => '13:00 – 17:00 ДОСТАВКА',
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'СРЕДА',
                'is_day_off' => false,
                'day_off_text' => null,
                'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                'delivery' => '13:00 – 17:00 ДОСТАВКА',
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'ЧЕТВЕРГ',
                'is_day_off' => true,
                'day_off_text' => 'ВСЕГДА ВЫХОДНОЙ',
                'workshop' => null,
                'delivery' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'ПЯТНИЦА',
                'is_day_off' => false,
                'day_off_text' => null,
                'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                'delivery' => '13:00 – 17:00 ДОСТАВКА',
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => 'СУББОТА',
                'is_day_off' => false,
                'day_off_text' => null,
                'workshop' => '11:00 – 17:00 МАСТЕРСКАЯ',
                'delivery' => '13:00 – 17:00 ДОСТАВКА',
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'name' => 'ВОСКРЕСЕНЬЕ',
                'is_day_off' => true,
                'day_off_text' => 'ВСЕГДА ВЫХОДНОЙ',
                'workshop' => null,
                'delivery' => null,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $maxId = (int) (DB::table('site_schedule_days')->max('id') ?? 0);
        DB::table('entity_id_sequences')->updateOrInsert(
            ['name' => 'site_schedule_day'],
            ['next_value' => $maxId + 1],
        );
    }
}
