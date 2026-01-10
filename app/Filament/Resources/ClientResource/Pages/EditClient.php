<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Если пароль пустой, удаляем его из данных, чтобы не перезаписывать существующий
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            // Хешируем пароль перед сохранением
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
