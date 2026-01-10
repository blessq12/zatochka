<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Хешируем пароль перед созданием
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Убираем @ из начала telegram username
        if (isset($data['telegram']) && is_string($data['telegram'])) {
            $data['telegram'] = ltrim($data['telegram'], '@');
            // Конвертируем пустую строку в NULL для nullable поля
            if (empty($data['telegram'])) {
                $data['telegram'] = null;
            }
        }

        // Конвертируем пустую строку email в NULL для nullable поля
        if (isset($data['email']) && empty($data['email'])) {
            $data['email'] = null;
        }

        return $data;
    }
}
