<?php

namespace App\Filament\Resources\TelegramMessageResource\Pages;

use App\Filament\Resources\TelegramMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTelegramMessage extends ViewRecord
{
    protected static string $resource = TelegramMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
