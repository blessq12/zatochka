<?php

namespace App\Filament\Resources\Manager\UserResource\Pages;

use App\Filament\Resources\Manager\UserResource;
use App\Application\UseCases\Company\User\UpdateUserUseCase;
use App\Application\UseCases\Company\User\DeleteUserUseCase;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->before(function () {
                    try {
                        $useCase = app(DeleteUserUseCase::class);
                        $useCase->loadData(['id' => $this->record->id])->validate();
                        $useCase->execute();

                        Notification::make()
                            ->title('Пользователь успешно удален')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                        $this->halt();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка при удалении пользователя')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        $this->halt();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            $data['id'] = $this->record->id;

            $useCase = app(UpdateUserUseCase::class);
            $user = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Пользователь успешно обновлен')
                ->success()
                ->send();

            // Возвращаем обновленные данные для Eloquent
            return [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_deleted' => $user->isDeleted(),
                'email_verified_at' => $user->getEmailVerifiedAt(),
            ];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при обновлении пользователя')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
