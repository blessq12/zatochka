<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Branch;
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
                    'legal_name' => 'ИНДИВИДУАЛЬНЫЙ ПРЕДПРИНИМАТЕЛЬ МИТЬКИН МАКСИМ ИГОРЕВИЧ',
                    'inn' => '701744164429',
                    'kpp' => null,
                    'ogrn' => '323700000001333',
                    'legal_address' => 'г. Томск, Ленина проспект, д. 44, пом 24-83',
                    'description' => 'Профессиональная заточка инструментов и режущего оборудования. Работаем с 2010 года.',
                    'website' => 'https://zatochka.org',
                    'phone' => '+7 (3822) 977-977',
                    'email' => 'info@zatochka.org',
                    'bank_name' => 'ТОМСКОЕ ОТДЕЛЕНИЕ N8616 ПАО СБЕРБАНК',
                    'bank_bik' => '046902606',
                    'bank_account' => '40802810364000021377',
                    'bank_cor_account' => '30101810800000000606',
                    'logo_path' => null,
                    'is_active' => true,
                    'additional_data' => json_encode([
                        'bank_inn' => '7707083893',
                        'bank_kpp' => '701702003',
                        'short_legal_name' => 'ИП Митькин М.И.',
                        'account_open_date' => '18.01.2023',
                        'services' => [
                            'Заточка ножей',
                            'Заточка ножниц',
                            'Заточка стамесок',
                            'Заточка рубанков',
                            'Заточка сверл',
                            'Заточка фрез',
                            'Заточка пил',
                            'Заточка цепей'
                        ],
                        'specializations' => [
                            'Бытовые инструменты',
                            'Профессиональные инструменты',
                            'Строительные инструменты',
                            'Столярные инструменты',
                            'Слесарные инструменты'
                        ]
                    ]),
                ],
            );

            // Главный филиал
            Branch::where(['code' => 'main'])->exists() ?:
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
                        'working_schedule' => json_encode([
                            'monday' => [
                                'is_working' => true,
                                'start' => '10:00',
                                'end' => '19:00',
                                'note' => 'Рабочий день'
                            ],
                            'tuesday' => [
                                'is_working' => true,
                                'start' => '10:00',
                                'end' => '19:00',
                                'note' => 'Рабочий день'
                            ],
                            'wednesday' => [
                                'is_working' => true,
                                'start' => '10:00',
                                'end' => '19:00',
                                'note' => 'Рабочий день'
                            ],
                            'thursday' => [
                                'is_working' => true,
                                'start' => '10:00',
                                'end' => '19:00',
                                'note' => 'Рабочий день'
                            ],
                            'friday' => [
                                'is_working' => true,
                                'start' => '10:00',
                                'end' => '19:00',
                                'note' => 'Рабочий день'
                            ],
                            'saturday' => [
                                'is_working' => true,
                                'start' => '11:00',
                                'end' => '16:00',
                                'note' => 'Сокращенный день'
                            ],
                            'sunday' => [
                                'is_working' => false,
                                'start' => null,
                                'end' => null,
                                'note' => 'Выходной'
                            ]
                        ]),
                        'opening_time' => '10:00',
                        'closing_time' => '19:00',
                        'latitude' => 56.4977,
                        'longitude' => 84.9744,
                        'description' => 'Главный филиал компании. Полный спектр услуг по заточке инструментов.',
                        'is_active' => true,
                        'is_main' => true,
                        'sort_order' => 1,
                        'additional_data' => json_encode([
                            'is_main' => true,
                            'services_available' => [
                                'Заточка ножей',
                                'Заточка ножниц',
                                'Заточка стамесок',
                                'Заточка рубанков',
                                'Заточка сверл',
                                'Заточка фрез',
                                'Заточка пил',
                                'Заточка цепей'
                            ],
                            'equipment' => [
                                'Точильные станки',
                                'Абразивные материалы',
                                'Измерительные инструменты',
                                'Защитное оборудование'
                            ],
                            'specialists' => 3,
                            'max_capacity_per_day' => 50
                        ]),
                    ]
                );

            // Дополнительный филиал
            Branch::where(['code' => 'north'])->exists() ?:
                Branch::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => 'north',
                    ],
                    [
                        'name' => 'Северный филиал',
                        'address' => 'г. Томск, ул. Красноармейская, д. 120, пом 15',
                        'phone' => '+7 (3822) 977-978',
                        'email' => 'north@zatochka.org',
                        'working_hours' => 'Пн-Пт: 09:00-18:00, Сб: 10:00-15:00',
                        'working_schedule' => json_encode([
                            'monday' => [
                                'is_working' => true,
                                'start' => '09:00',
                                'end' => '18:00',
                                'note' => 'Рабочий день'
                            ],
                            'tuesday' => [
                                'is_working' => true,
                                'start' => '09:00',
                                'end' => '18:00',
                                'note' => 'Рабочий день'
                            ],
                            'wednesday' => [
                                'is_working' => true,
                                'start' => '09:00',
                                'end' => '18:00',
                                'note' => 'Рабочий день'
                            ],
                            'thursday' => [
                                'is_working' => true,
                                'start' => '09:00',
                                'end' => '18:00',
                                'note' => 'Рабочий день'
                            ],
                            'friday' => [
                                'is_working' => true,
                                'start' => '09:00',
                                'end' => '18:00',
                                'note' => 'Рабочий день'
                            ],
                            'saturday' => [
                                'is_working' => true,
                                'start' => '10:00',
                                'end' => '15:00',
                                'note' => 'Сокращенный день'
                            ],
                            'sunday' => [
                                'is_working' => false,
                                'start' => null,
                                'end' => null,
                                'note' => 'Выходной'
                            ]
                        ]),
                        'opening_time' => '09:00',
                        'closing_time' => '18:00',
                        'latitude' => 56.5123,
                        'longitude' => 84.9876,
                        'description' => 'Северный филиал компании. Специализация на бытовых инструментах.',
                        'is_active' => true,
                        'is_main' => false,
                        'sort_order' => 2,
                        'additional_data' => json_encode([
                            'is_main' => false,
                            'services_available' => [
                                'Заточка ножей',
                                'Заточка ножниц',
                                'Заточка стамесок',
                                'Заточка рубанков'
                            ],
                            'equipment' => [
                                'Точильные станки',
                                'Абразивные материалы',
                                'Измерительные инструменты'
                            ],
                            'specialists' => 2,
                            'max_capacity_per_day' => 30
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
