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

        // Временный пароль: при вводе хешируем и сбрасываем флаг «использован»
        if (\Illuminate\Support\Str::length($data['new_temporary_password'] ?? '') > 0) {
            $data['temporary_password'] = Hash::make($data['new_temporary_password']);
            $data['temporary_password_used'] = false;
        }
        unset($data['new_temporary_password']);

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

        // Комментарии менеджера: только добавляем новые к существующим (лог, без перезаписи)
        $existing = $this->record->manager_comments ?? [];
        $formComments = $data['manager_comments'] ?? [];
        $newItems = [];
        foreach ($formComments as $item) {
            if (is_array($item) && empty($item['created_at']) && ! empty(trim($item['text'] ?? ''))) {
                $user = auth()->user();
                $newItems[] = [
                    'user_id' => $user?->id,
                    'author_name' => $user?->name ?? 'Менеджер',
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'text' => trim($item['text']),
                ];
            }
        }
        $data['manager_comments'] = array_merge($existing, $newItems);

        return $data;
    }
}
