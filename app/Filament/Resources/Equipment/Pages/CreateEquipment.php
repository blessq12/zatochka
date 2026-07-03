<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Support\EquipmentFormData;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: $data['name'],
            serialNumbers: EquipmentFormData::serialNumbersFromForm($data),
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
        ));

        return EquipmentModel::query()->findOrFail($equipment->id());
    }
}
