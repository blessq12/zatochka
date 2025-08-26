<?php

namespace App\Filament\Resources\ClientBonusResource\Pages;

use App\Filament\Resources\ClientBonusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientBonuses extends ListRecords
{
    protected static string $resource = ClientBonusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
