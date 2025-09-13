<?php

namespace App\Filament\Resources\Manager\ClientResource\Pages;

use App\Application\UseCases\Client\CreateClientUseCase;
use App\Filament\Resources\Manager\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function handleRecordCreation(array $data): \App\Models\Client
    {
        try {
            $useCase = app(CreateClientUseCase::class);
            $clientEntity = $useCase->loadData($data)->validate()->execute();

            // Возвращаем Eloquent модель для Filament
            return \App\Models\Client::find($clientEntity->getId());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка создания клиента')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
