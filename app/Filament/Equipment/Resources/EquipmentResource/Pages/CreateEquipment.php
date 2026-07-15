<?php

namespace App\Filament\Equipment\Resources\EquipmentResource\Pages;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Equipment\DTO\EquipmentPartDTO;
use App\Filament\Equipment\Resources\EquipmentResource;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected static ?string $title = 'Новое оборудование';

    protected function handleRecordCreation(array $data): Model
    {
        $ids = app(SequentialEntityIdGenerator::class);
        $equipmentId = $ids->next('equipment')->value;

        $parts = [];

        foreach ($data['parts'] ?? [] as $part) {
            $parts[] = new EquipmentPartDTO(
                $ids->next('equipment_component')->value,
                $part['name'],
                $part['serialNumber'] ?? null,
            );
        }

        app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            $equipmentId,
            $data['title'],
            $data['brand'],
            $data['model_name'],
            filled($data['client_id'] ?? null) ? (int) $data['client_id'] : null,
            $data['notes'] ?? null,
            $parts,
        ));

        return ClientEquipmentModel::query()->findOrFail($equipmentId);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Оборудование добавлено';
    }
}
