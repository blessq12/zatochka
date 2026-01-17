<?php

namespace App\Filament\Resources\MasterResource\Pages;

use App\Filament\Resources\MasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaster extends ViewRecord
{
    protected static string $resource = MasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
