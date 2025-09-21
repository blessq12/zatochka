<?php

namespace App\Filament\Resources\Manager\CompanyResource\Pages;

use App\Filament\Resources\Manager\CompanyResource;
use App\Models\Company;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    public function mount(): void
    {
        // Получаем первую активную компанию
        $company = Company::where('is_deleted', false)->first();

        if ($company) {
            // Редиректим сразу на редактирование
            $this->redirect(static::getResource()::getUrl('edit', ['record' => $company]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            // Убираем кнопку создания
        ];
    }
}
