<?php

namespace App\Filament\Resources\Managers\Pages;

use App\Domain\Identity\Enum\UserRole;
use App\Filament\Resources\Managers\ManagerResource;
use App\Filament\Resources\Managers\Schemas\ManagerForm;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateManager extends CreateRecord
{
    protected static string $resource = ManagerResource::class;

    public function form(Schema $schema): Schema
    {
        return ManagerForm::configure($schema, isCreate: true);
    }

    protected function handleRecordCreation(array $data): Model
    {
        if (UserModel::query()->where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Менеджер с таким email уже существует.',
            ]);
        }

        return UserModel::query()->create([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? '',
            'email' => $data['email'],
            'role' => UserRole::Manager,
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'notifications_enabled' => false,
        ]);
    }
}
