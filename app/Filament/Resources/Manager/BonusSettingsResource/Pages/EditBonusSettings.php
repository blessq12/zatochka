<?php

namespace App\Filament\Resources\Manager\BonusSettingsResource\Pages;

use App\Filament\Resources\Manager\BonusSettingsResource;
use App\Models\BonusSettings;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBonusSettings extends EditRecord
{
    protected static string $resource = BonusSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Убираем View и Delete - только редактирование
        ];
    }

    protected function resolveRecord(string|int $key): BonusSettings
    {
        // Всегда возвращаем единственную запись настроек, игнорируя $key
        return BonusSettings::getSettings();
    }

    public function getTitle(): string
    {
        return 'Настройки бонусной системы';
    }
}
