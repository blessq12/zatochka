<?php

namespace App\Filament\Resources\Master\ToolResource\Pages;

use App\Filament\Resources\Master\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTool extends ViewRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
