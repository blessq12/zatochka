<?php

namespace App\Filament\Resources\Masters\Pages;

use App\Application\Identity\Command\UpdateMasterCommand;
use App\Application\Identity\CommandHandler\UpdateMasterHandler;
use App\Domain\Identity\Exception\MasterAlreadyExistsException;
use App\Filament\Resources\Masters\MasterResource;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditMaster extends EditRecord
{
    protected static string $resource = MasterResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var UserModel $record */
        try {
            $master = app(UpdateMasterHandler::class)->handle(new UpdateMasterCommand(
                id: $record->id,
                name: $data['name'],
                surname: $data['surname'] ?? '',
                email: $data['email'],
                phone: $data['phone'] ?? null,
                password: $data['password'] ?? null,
                notificationsEnabled: (bool) ($data['notifications_enabled'] ?? false),
            ));
        } catch (MasterAlreadyExistsException $exception) {
            throw ValidationException::withMessages([
                'email' => $exception->getMessage(),
            ]);
        }

        return UserModel::query()->findOrFail($master->id());
    }
}
