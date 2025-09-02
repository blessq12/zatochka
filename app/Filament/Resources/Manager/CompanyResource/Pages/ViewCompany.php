<?php

namespace App\Filament\Resources\Manager\CompanyResource\Pages;

use App\Filament\Resources\Manager\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompany extends ViewRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
        ];
    }
}
