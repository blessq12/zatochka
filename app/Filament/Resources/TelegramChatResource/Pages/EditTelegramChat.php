<?php

namespace App\Filament\Resources\TelegramChatResource\Pages;

use App\Filament\Resources\TelegramChatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTelegramChat extends EditRecord
{
    protected static string $resource = TelegramChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
