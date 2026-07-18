<?php

namespace App\Filament\CRM\Resources\ClientResource\Pages;

use App\Filament\CRM\Resources\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Клиенты';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить клиента')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
