<?php

namespace App\Filament\Resources\Manager\CompanyResource\Pages;

use App\Filament\Resources\Manager\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
