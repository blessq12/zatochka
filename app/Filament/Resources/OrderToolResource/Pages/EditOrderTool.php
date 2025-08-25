<?php

namespace App\Filament\Resources\OrderToolResource\Pages;

use App\Filament\Resources\OrderToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderTool extends EditRecord
{
    protected static string $resource = OrderToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
