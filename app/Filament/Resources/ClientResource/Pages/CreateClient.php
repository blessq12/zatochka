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

        // Временный пароль: при вводе хешируем и выставляем флаг «не использован»
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

        // Комментарии менеджера: для записей без created_at проставляем автора и дату
        if (! empty($data['manager_comments']) && is_array($data['manager_comments'])) {
            $user = auth()->user();
            $now = now()->format('Y-m-d H:i:s');
            foreach ($data['manager_comments'] as $key => $item) {
                if (is_array($item) && empty($item['created_at']) && ! empty(trim($item['text'] ?? ''))) {
                    $data['manager_comments'][$key]['user_id'] = $user?->id;
                    $data['manager_comments'][$key]['author_name'] = $user?->name ?? 'Менеджер';
                    $data['manager_comments'][$key]['created_at'] = $now;
                }
            }
        }

        return $data;
    }
}
