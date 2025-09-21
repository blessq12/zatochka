<?php

namespace App\Filament\Resources\Master\ToolResource\Pages;

use App\Filament\Resources\Master\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
