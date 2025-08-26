<?php

namespace App\Filament\Resources\ClientBonusResource\Pages;

use App\Filament\Resources\ClientBonusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientBonus extends EditRecord
{
    protected static string $resource = ClientBonusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
