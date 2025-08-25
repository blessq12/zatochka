<?php

namespace App\Filament\Resources\TelegramChatResource\Pages;

use App\Filament\Resources\TelegramChatResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTelegramChat extends ViewRecord
{
    protected static string $resource = TelegramChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Убираем редактирование для системных таблиц
        ];
    }
}
