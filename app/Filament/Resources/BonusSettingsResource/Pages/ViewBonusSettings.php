<?php

namespace App\Filament\Resources\BonusSettingsResource\Pages;

use App\Filament\Resources\BonusSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBonusSettings extends ViewRecord
{
    protected static string $resource = BonusSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
