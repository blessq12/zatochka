<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_company_profiles')->updateOrInsert(
            ['id' => 1],
            [
                'owner_name' => 'ИП Митькин Максим Игоревич',
                'inn' => '701744164429',
                'ogrn' => '323700000001333',
                'legal_address' => '634033, Томская обл., г. Томск, ул. Короленко, д. 17, кв. 12',
                'actual_address' => 'пер. Карповский, 12 / пр. Ленина, 169',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }
}
