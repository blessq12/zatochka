<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $serials = [];
        foreach ($data['serial_numbers'] ?? [] as $row) {
            $value = is_array($row) ? ($row['value'] ?? null) : $row;
            if (is_string($value) && $value !== '') {
                $serials[] = $value;
            }
        }

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: $data['name'],
            serialNumbers: $serials,
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
        ));

        return EquipmentModel::query()->findOrFail($equipment->id());
    }
}
