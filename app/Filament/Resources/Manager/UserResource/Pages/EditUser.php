<?php

namespace App\Filament\Resources\Manager\UserResource\Pages;

use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Domain\Users\ValueObjects\Email;
// ... existing code ...
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
            (int) $record->id
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
            // Логирование ролей пользователя
            $domainUser->replaceRoles($data['roles']);
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
