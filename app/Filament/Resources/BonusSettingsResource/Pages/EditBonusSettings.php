<?php

namespace App\Filament\Resources\BonusSettingsResource\Pages;

use App\Filament\Resources\BonusSettingsResource;
use App\Models\BonusSettings;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBonusSettings extends EditRecord
{
    protected static string $resource = BonusSettingsResource::class;

    public function mount(int | string | null $record = null): void
    {
        // Всегда используем единственную запись настроек
        $settings = BonusSettings::getSettings();
        parent::mount($settings->id);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Нет действий для настроек - только редактирование
        ];
    }
}
