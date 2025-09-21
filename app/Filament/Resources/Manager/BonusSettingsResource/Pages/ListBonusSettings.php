<?php

namespace App\Filament\Resources\Manager\BonusSettingsResource\Pages;

use App\Filament\Resources\Manager\BonusSettingsResource;
use App\Models\BonusSettings;
use Filament\Resources\Pages\ListRecords;

class ListBonusSettings extends ListRecords
{
    protected static string $resource = BonusSettingsResource::class;

    public function mount(): void
    {

        $settings = BonusSettings::getSettings();
        $this->redirect(static::getResource()::getUrl('edit', ['record' => $settings]));
    }

    protected function getHeaderActions(): array
    {
        return [
            // Убираем кнопку создания
        ];
    }
}
