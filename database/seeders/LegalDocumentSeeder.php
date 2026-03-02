<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Компания не найдена. Сначала запустите CompanyAndBranchSeeder.');

            return;
        }

        $documents = [
            LegalDocument::TYPE_PRIVACY_POLICY => [
                'title' => 'Политика конфиденциальности',
                'content' => '<p>Здесь размещается текст политики конфиденциальности. Отредактируйте документ в админ-панели.</p>',
            ],
            LegalDocument::TYPE_USER_AGREEMENT => [
                'title' => 'Пользовательское соглашение',
                'content' => '<p>Здесь размещается текст пользовательского соглашения. Отредактируйте документ в админ-панели.</p>',
            ],
            LegalDocument::TYPE_PERSONAL_DATA_PROCESSING => [
                'title' => 'Обработка персональных данных',
                'content' => '<p>Здесь размещается текст о порядке обработки персональных данных. Отредактируйте документ в админ-панели.</p>',
            ],
            LegalDocument::TYPE_TERMS_OF_USE => [
                'title' => 'Условия использования ресурса',
                'content' => '<p>Здесь размещаются условия использования ресурса. Отредактируйте документ в админ-панели.</p>',
            ],
        ];

        foreach ($documents as $type => $data) {
            LegalDocument::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'type' => $type,
                ],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'version' => '1.0',
                ]
            );
        }
    }
}
