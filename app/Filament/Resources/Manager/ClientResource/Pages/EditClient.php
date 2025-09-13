<?php

namespace App\Filament\Resources\Manager\ClientResource\Pages;

use App\Application\UseCases\Client\UpdateClientUseCase;
use App\Filament\Resources\Manager\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            $data['id'] = $this->record->id;
            $useCase = app(UpdateClientUseCase::class);
            $useCase->loadData($data)->validate()->execute();

            return $data;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка обновления клиента')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
