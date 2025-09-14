<?php

namespace App\Filament\Resources\Manager\CompanyResource\Pages;

use App\Filament\Resources\Manager\CompanyResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Если есть только одна компания, сразу редиректим на редактирование
        $companyCount = Company::count();

        if ($companyCount === 1) {
            $company = Company::first();
            $this->redirect(static::getResource()::getUrl('edit', ['record' => $company]));
        }

        return parent::getTableQuery();
    }
}
