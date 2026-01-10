<?php

namespace App\Filament\Resources\EquipmentTypeResource\Pages;

use App\Filament\Resources\EquipmentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipmentType extends ViewRecord
{
    protected static string $resource = EquipmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
