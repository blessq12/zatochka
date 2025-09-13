<?php

namespace App\Filament\Resources\Manager\BonusSettingsResource\Pages;

use App\Filament\Resources\Manager\BonusSettingsResource;
use Filament\Resources\Pages\ListRecords;

class ListBonusSettings extends ListRecords
{
    protected static string $resource = BonusSettingsResource::class;

    public function mount(): void
    {
        $settings = \App\Models\BonusSettings::getSettings();
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $settings->id]));
    }
}
