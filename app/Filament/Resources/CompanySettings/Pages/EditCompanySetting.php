<?php

namespace App\Filament\Resources\CompanySettings\Pages;

use App\Filament\Resources\CompanySettings\CompanySettingResource;
use App\Filament\Resources\CompanySettings\Tables\CompanySettingsTable;
use App\Infrastructure\Company\Persistence\Eloquent\CompanySettingModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditCompanySetting extends EditRecord
{
    protected static string $resource = CompanySettingResource::class;

    public function getTitle(): string
    {
        /** @var CompanySettingModel $record */
        $record = $this->getRecord();

        return CompanySettingsTable::labelForKey($record->key);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var CompanySettingModel $record */
        $record = $this->getRecord();

        $data['value_json'] = json_encode(
            $record->value ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var CompanySettingModel $record */
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

        $record->update(['value' => $value]);

        return $record;
    }
}
