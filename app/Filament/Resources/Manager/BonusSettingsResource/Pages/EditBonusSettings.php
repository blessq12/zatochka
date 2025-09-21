<?php

namespace App\Filament\Resources\Manager\BonusSettingsResource\Pages;

use App\Filament\Resources\Manager\BonusSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBonusSettings extends EditRecord
{
    protected static string $resource = BonusSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
