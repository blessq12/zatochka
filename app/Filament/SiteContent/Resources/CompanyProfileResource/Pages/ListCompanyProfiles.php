<?php

namespace App\Filament\SiteContent\Resources\CompanyProfileResource\Pages;

use App\Filament\SiteContent\Resources\CompanyProfileResource;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListCompanyProfiles extends ListRecords
{
    protected static string $resource = CompanyProfileResource::class;

    protected static ?string $title = 'Компания';

    public function mount(): void
    {
        $record = CompanyProfileModel::query()->find(1);

        if ($record instanceof Model) {
            $this->redirect(CompanyProfileResource::getUrl('edit', ['record' => $record]));
        }
    }
}
