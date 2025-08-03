<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run()
    {
        try {
            DB::beginTransaction();

            $company = Company::firstOrCreate(
                [
                    'name' => 'Заточка Томск',
                    'legal_name' => 'ИНДИВИДУАЛЬНЫЙ ПРЕДПРИНИМАТЕЛЬ МИТЬКИН МАКСИМ ИГОРЕВИЧ',
                    'inn' => '701744164429',
                    'kpp' => null,
                    'ogrn' => '323700000001333',
                    'legal_address' => 'г. Томск, Ленина проспект, д. 44, пом 24-83',
                    'website' => null,
                    'bank_name' => 'ТОМСКОЕ ОТДЕЛЕНИЕ N8616 ПАО СБЕРБАНК',
                    'bank_bik' => '046902606',
                    'bank_account' => '40802810364000021377',
                    'bank_cor_account' => '30101810800000000606',
                    'description' => 'Профессиональная заточка инструментов',
                    'logo_path' => null,
                    'additional_data' => json_encode([
                        'bank_inn' => '7707083893',
                        'bank_kpp' => '701702003',
                        'short_legal_name' => 'ИП Митькин М.И.',
                        'account_open_date' => '18.01.2023'
                    ]),
                ],
            );

            Branch::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => 'main',
                ],
                [
                    'name' => 'Главный филиал',
                    'address' => 'г. Томск, Ленина проспект, д. 44, пом 24-83',
                    'phone' => '+7 (3822) 977-977',
                    'email' => 'info@zatochka.org',
                    'working_hours' => 'Пн-Пт: 10:00-19:00, Сб: 11:00-16:00',
                    'description' => 'Главный филиал компании',
                    'is_active' => true,
                    'additional_data' => json_encode([
                        'is_main' => true,
                    ]),
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
