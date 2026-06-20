<?php

namespace App\Filament\Resources\Masters\Pages;

use App\Domain\Identity\Enum\UserRole;
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
        if (UserModel::query()->where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Мастер с таким email уже существует.',
            ]);
        }

        return UserModel::query()->create([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? '',
            'email' => $data['email'],
            'role' => UserRole::Master,
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'notifications_enabled' => (bool) ($data['notifications_enabled'] ?? false),
        ]);
    }
}
