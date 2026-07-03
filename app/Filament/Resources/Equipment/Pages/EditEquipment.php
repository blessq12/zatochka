<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Application\Equipment\Command\UpdateEquipmentCommand;
use App\Application\Equipment\CommandHandler\UpdateEquipmentHandler;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Support\EquipmentFormData;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return EquipmentFormData::prepareForForm($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var EquipmentModel $record */
        app(UpdateEquipmentHandler::class)->handle(new UpdateEquipmentCommand(
            equipmentId: (int) $record->getKey(),
            name: $data['name'],
            serialNumbers: EquipmentFormData::serialNumbersFromForm($data),
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
        ));

        return EquipmentModel::query()->findOrFail($record->getKey());
    }
}
