<?php

namespace App\Filament\Equipment\Resources\EquipmentResource\Pages;

use App\Filament\Equipment\Resources\EquipmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEquipments extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить оборудование'),
        ];
    }
}
