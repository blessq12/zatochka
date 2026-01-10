<?php

namespace App\Filament\Resources\BonusAccountResource\Pages;

use App\Filament\Resources\BonusAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonusAccounts extends ListRecords
{
    protected static string $resource = BonusAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
