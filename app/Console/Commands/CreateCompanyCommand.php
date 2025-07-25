<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Branch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateCompanyCommand extends Command
{
    protected $signature = 'company:create';
    protected $description = 'Создает компанию и главный филиал с дефолтными данными';

    public function handle(): int
    {
        $this->info('Создаем компанию и главный филиал...');

        try {
            DB::beginTransaction();

            $company = Company::create([
                'name' => 'Заточка Томск',
                'legal_name' => 'ИНДИВИДУАЛЬНЫЙ ПРЕДПРИНИМАТЕЛЬ МИТЬКИН МАКСИМ ИГОРЕВИЧ',
                'inn' => '701744164429',
                'ogrn' => '323700000001333',
                'legal_address' => 'г. Томск, Ленина проспект, д. 44, пом 24-83',
                'bank_name' => 'ТОМСКОЕ ОТДЕЛЕНИЕ N8616 ПАО СБЕРБАНК',
                'bank_bik' => '046902606',
                'bank_account' => '40802810364000021377',
                'bank_cor_account' => '30101810800000000606',
                'description' => 'Профессиональная заточка инструментов',
                'additional_data' => [
                    'short_legal_name' => 'ИП Митькин М.И.',
                    'bank_inn' => '7707083893',
                    'bank_kpp' => '701702003',
                    'account_open_date' => '18.01.2023',
                ],
            ]);

            $branch = Branch::create([
                'company_id' => $company->id,
                'name' => 'Главный филиал',
                'code' => 'main',
                'address' => 'г. Томск, Ленина проспект, д. 44, пом 24-83',
                'phone' => '+7 (3822) 977-977',
                'email' => 'info@zatochka.org',
                'working_hours' => 'Пн-Пт: 10:00-19:00, Сб: 11:00-16:00',
                'description' => 'Главный филиал компании',
                'is_active' => true,
                'additional_data' => [
                    'is_main' => true,
                ],
            ]);

            DB::commit();

            $this->info('✅ Компания и главный филиал успешно созданы!');

            $this->table(
                ['ID', 'Название', 'Короткое юр. имя', 'ИНН', 'ОГРН'],
                [[
                    $company->id,
                    $company->name,
                    $company->additional_data['short_legal_name'],
                    $company->inn,
                    $company->ogrn
                ]]
            );

            $this->table(
                ['ID', 'Филиал', 'Код', 'Адрес', 'Телефон'],
                [[
                    $branch->id,
                    $branch->name,
                    $branch->code,
                    $branch->address,
                    $branch->phone
                ]]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Ошибка при создании компании: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
