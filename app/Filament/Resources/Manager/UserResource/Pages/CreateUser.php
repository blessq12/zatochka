<?php

namespace App\Filament\Resources\Manager\UserResource\Pages;

use App\Filament\Resources\Manager\UserResource;
use App\Application\UseCases\Company\User\CreateUserUseCase;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $useCase = app(CreateUserUseCase::class);
            $user = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Пользователь успешно создан')
                ->success()
                ->send();

            // Возвращаем Eloquent модель по ID из Domain Entity
            return \App\Models\User::find($user->getId());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при создании пользователя')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
