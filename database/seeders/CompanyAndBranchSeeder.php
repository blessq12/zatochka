<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyAndBranchSeeder extends Seeder
{
    public function run()
    {
        try {
            DB::beginTransaction();

            $company = Company::firstOrCreate(
                [
                    'name' => 'Заточка Томск',
                ],
                [
                    'legal_name' => 'ИНДИВИДУАЛЬНЫЙ ПРЕДПРИНИМАТЕЛЬ МИТЬКИН МАКСИМ ИГОРЕВИЧ',
                    'inn' => '701744164429',
                    'kpp' => null,
                    'ogrn' => '323700000001333',
                    'legal_address' => 'г. Томск, Ленина проспект, д. 44, пом 24-83',
                    'description' => 'Профессиональная заточка инструментов и режущего оборудования. Работаем с 2010 года.',
                    'bank_name' => 'ТОМСКОЕ ОТДЕЛЕНИЕ N8616 ПАО СБЕРБАНК',
                    'bank_bik' => '046902606',
                    'bank_account' => '40802810364000021377',
                    'bank_cor_account' => '30101810800000000606',
                    'is_active' => true,
                    'is_deleted' => false,
                ]
            );

            // Главный филиал
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
                    'working_schedule' => [
                        'monday' => [
                            'is_working' => true,
                            'start' => '10:00',
                            'end' => '19:00',
                        ],
                        'tuesday' => [
                            'is_working' => true,
                            'start' => '10:00',
                            'end' => '19:00',
                        ],
                        'wednesday' => [
                            'is_working' => true,
                            'start' => '10:00',
                            'end' => '19:00',
                        ],
                        'thursday' => [
                            'is_working' => true,
                            'start' => '10:00',
                            'end' => '19:00',
                        ],
                        'friday' => [
                            'is_working' => true,
                            'start' => '10:00',
                            'end' => '19:00',
                        ],
                        'saturday' => [
                            'is_working' => true,
                            'start' => '11:00',
                            'end' => '16:00',
                        ],
                        'sunday' => [
                            'is_working' => false,
                            'start' => null,
                            'end' => null,
                        ],
                    ],
                    'opening_time' => '10:00',
                    'closing_time' => '19:00',
                    'latitude' => 56.4977,
                    'longitude' => 84.9744,
                    'description' => 'Главный филиал компании. Полный спектр услуг по заточке инструментов.',
                    'is_active' => true,
                    'is_main' => true,
                    'sort_order' => 1,
                    'is_deleted' => false,
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
