<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $serials = [];

        foreach ($data['serial_numbers'] ?? [] as $row) {
            $value = is_array($row) ? ($row['value'] ?? null) : $row;

            if (is_string($value) && $value !== '') {
                $serials[] = $value;
            }
        }

        $data['serial_numbers'] = $serials;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return EquipmentModel::query()->create($data);
    }
}
