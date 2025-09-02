<?php

namespace App\Filament\Resources\Manager\UserResource\Pages;

use App\Domain\Users\Services\UserDomainService;
use App\Domain\Users\ValueObjects\Email;
use App\Filament\Resources\Manager\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $domainService = app(UserDomainService::class);

        $user = $domainService->register(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['roles'] ?? []
        );

        // Получаем Eloquent-модель по UUID для отображения в UI
        return \App\Models\User::where('uuid', (string) $user->userId())->first();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
