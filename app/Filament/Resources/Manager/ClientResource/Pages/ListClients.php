<?php

namespace App\Filament\Resources\Manager\ClientResource\Pages;

use App\Application\UseCases\Client\DeleteClientUseCase;
use App\Filament\Resources\Manager\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->action(function ($record) {
                    try {
                        $useCase = app(DeleteClientUseCase::class);
                        $useCase->loadData(['id' => $record->id])->validate()->execute();

                        Notification::make()
                            ->title('Клиент удален')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка удаления клиента')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
