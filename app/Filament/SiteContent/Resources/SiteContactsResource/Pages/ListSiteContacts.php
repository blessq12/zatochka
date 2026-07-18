<?php

namespace App\Filament\SiteContent\Resources\SiteContactsResource\Pages;

use App\Filament\SiteContent\Resources\SiteContactsResource;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListSiteContacts extends ListRecords
{
    protected static string $resource = SiteContactsResource::class;

    protected static ?string $title = 'Контакты';

    public function mount(): void
    {
        $record = SiteContactsModel::query()->find(1);

        if ($record instanceof Model) {
            $this->redirect(SiteContactsResource::getUrl('edit', ['record' => $record]));
        }
    }
}
