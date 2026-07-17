<?php

namespace App\Filament\Identity\Resources\UserResource\Pages;

use App\Application\Identity\Command\RegisterStaffUserCommand;
use App\Application\Identity\Command\RegisterStaffUserHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Filament\Identity\Resources\UserResource;
use App\Models\User;
use App\Shared\Domain\DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Новый сотрудник';

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $userId = app(EntityIdGenerator::class)->next('user')->value;

            app(RegisterStaffUserHandler::class)->handle(new RegisterStaffUserCommand(
                $userId,
                (string) $data['name'],
                (string) $data['email'],
                (string) $data['role'],
                (string) $data['password'],
            ));

            return User::query()->findOrFail($userId);
        } catch (DomainException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw ValidationException::withMessages([
                'data.email' => $exception->getMessage(),
            ]);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Сотрудник создан';
    }
}
