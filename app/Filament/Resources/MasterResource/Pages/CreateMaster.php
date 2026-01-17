<?php

namespace App\Filament\Resources\MasterResource\Pages;

use App\Filament\Resources\MasterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaster extends CreateRecord
{
    protected static string $resource = MasterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
