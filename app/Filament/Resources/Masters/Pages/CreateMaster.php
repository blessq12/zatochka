<?php

namespace App\Filament\Resources\Masters\Pages;

use App\Application\Identity\Command\RegisterMasterCommand;
use App\Application\Identity\CommandHandler\RegisterMasterHandler;
use App\Domain\Identity\Exception\MasterAlreadyExistsException;
use App\Filament\Resources\Masters\MasterResource;
use App\Filament\Resources\Masters\Schemas\MasterForm;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateMaster extends CreateRecord
{
    protected static string $resource = MasterResource::class;

    public function form(Schema $schema): Schema
    {
        return MasterForm::configure($schema, isCreate: true);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $master = app(RegisterMasterHandler::class)->handle(new RegisterMasterCommand(
                name: $data['name'],
                surname: $data['surname'] ?? '',
                email: $data['email'],
                phone: $data['phone'] ?? null,
                password: $data['password'],
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
