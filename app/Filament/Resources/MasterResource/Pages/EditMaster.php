<?php

namespace App\Filament\Resources\MasterResource\Pages;

use App\Filament\Resources\MasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaster extends EditRecord
{
    protected static string $resource = MasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Убираем @ из начала telegram username
        if (isset($data['telegram_username']) && is_string($data['telegram_username'])) {
            $data['telegram_username'] = ltrim($data['telegram_username'], '@');
            if (empty($data['telegram_username'])) {
                $data['telegram_username'] = null;
            }
        }

        return $data;
    }
}
