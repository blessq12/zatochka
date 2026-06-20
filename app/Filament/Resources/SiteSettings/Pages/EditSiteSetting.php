<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Application\Catalog\Command\SaveSiteSettingCommand;
use App\Application\Catalog\CommandHandler\SaveSiteSettingHandler;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\SiteSettings\Tables\SiteSettingsTable;
use App\Infrastructure\Catalog\Persistence\Eloquent\SiteSettingModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    public function getTitle(): string
    {
        /** @var SiteSettingModel $record */
        $record = $this->getRecord();

        return SiteSettingsTable::labelForKey($record->key);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var SiteSettingModel $record */
        $record = $this->getRecord();

        $data['value_json'] = json_encode(
            $record->value ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var SiteSettingModel $record */
        try {
            $value = json_decode($data['value_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([
                'value_json' => 'Некорректный JSON.',
            ]);
        }

        if (! is_array($value)) {
            throw ValidationException::withMessages([
                'value_json' => 'JSON должен быть объектом или массивом.',
            ]);
        }

        $setting = app(SaveSiteSettingHandler::class)->handle(new SaveSiteSettingCommand(
            key: $record->key,
            value: $value,
        ));

        return SiteSettingModel::query()->findOrFail($setting->id());
    }
}
