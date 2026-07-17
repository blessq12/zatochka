<?php

namespace App\Filament\Identity\Resources\UserResource\Pages;

use App\Application\Identity\Command\UpdateStaffUserCommand;
use App\Application\Identity\Command\UpdateStaffUserHandler;
use App\Filament\Identity\Resources\UserResource;
use App\Shared\Domain\DomainException;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Редактирование сотрудника';

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(UpdateStaffUserHandler::class)->handle(new UpdateStaffUserCommand(
                (int) $record->getKey(),
                (string) $data['name'],
                (string) $data['email'],
                (string) $data['role'],
                filled($data['password'] ?? null) ? (string) $data['password'] : null,
            ));

            return $record->fresh() ?? $record;
        } catch (DomainException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw ValidationException::withMessages([
                'data.email' => $exception->getMessage(),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Удалить'),
        ];
    }
}
