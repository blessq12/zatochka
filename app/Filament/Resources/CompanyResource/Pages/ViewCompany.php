<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompany extends ViewRecord
{
    protected static string $resource = CompanyResource::class;

    public function mount(int | string | null $record = null): void
    {
        // Если запись не указана (главная страница), автоматически загружаем первую компанию
        if ($record === null) {
            $company = Company::first();
            if ($company) {
                $record = $company->id;
            } else {
                // Если компании нет, можно создать или показать ошибку
                abort(404, 'Компания не найдена. Создайте компанию.');
            }
        }

        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
