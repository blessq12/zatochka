<?php

namespace App\Filament\Resources\Manager\UserResource\Pages;

use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\UserId;
use App\Domain\Users\ValueObjects\PasswordHash;
use App\Filament\Resources\Manager\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $userRepo = app(UserRepositoryInterface::class);

        // Получаем доменного пользователя по UUID
        $domainUser = $userRepo->getById(
            UserId::fromString($record->uuid)
        );

        if (!$domainUser) {
            throw new \Exception('User not found in domain');
        }

        // Применяем изменения через доменные методы
        if (isset($data['name']) && $data['name'] !== $domainUser->name()) {
            $domainUser->rename($data['name']);
        }

        if (isset($data['email']) && $data['email'] !== (string) $domainUser->email()) {
            $domainUser->changeEmail(Email::fromString($data['email']));
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $hasher = app(\App\Domain\Shared\Interfaces\PasswordHasherInterface::class);
            $domainUser->setPassword($hasher->hash($data['password']));
        }

        if (isset($data['roles'])) {
            \Log::info('Roles data from form', [
                'form_roles' => $data['roles'],
                'current_domain_roles' => $domainUser->roles()
            ]);
            $domainUser->replaceRoles($data['roles']);
            \Log::info('Roles replaced in domain', [
                'new_roles' => $domainUser->roles()
            ]);
        }

        // Обрабатываем статус удаления
        $isDeleted = $data['is_deleted'] ?? false;
        if ($isDeleted && !$domainUser->isDeleted()) {
            $domainUser->markDeleted();
        } elseif (!$isDeleted && $domainUser->isDeleted()) {
            $domainUser->activate();
        }

        // Сохраняем через репозиторий
        $userRepo->save($domainUser);

        // Возвращаем обновленную Eloquent-модель
        return \App\Models\User::where('uuid', (string) $domainUser->userId())->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Просмотр'),
            Actions\DeleteAction::make()
                ->label('Удалить'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
