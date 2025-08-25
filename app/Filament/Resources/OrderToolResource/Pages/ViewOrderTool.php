<?php

namespace App\Filament\Resources\OrderToolResource\Pages;

use App\Filament\Resources\OrderToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderTool extends ViewRecord
{
    protected static string $resource = OrderToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
