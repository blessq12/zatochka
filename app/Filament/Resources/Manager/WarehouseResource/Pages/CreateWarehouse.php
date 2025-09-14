<?php

namespace App\Filament\Resources\Manager\WarehouseResource\Pages;

use App\Application\UseCases\Warehouse\Warehouse\CreateWarehouseUseCase;
use App\Filament\Resources\Manager\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $useCase = app(CreateWarehouseUseCase::class);
            $warehouse = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Склад успешно создан')
                ->success()
                ->send();

            // Возвращаем Eloquent модель по ID из Domain Entity
            return \App\Models\Warehouse::find($warehouse->getId());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при создании склада')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
