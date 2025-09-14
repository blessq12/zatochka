<?php

namespace App\Filament\Resources\Manager\StockItemResource\Pages;

use App\Filament\Resources\Manager\StockItemResource;
use App\Application\UseCases\Warehouse\StockItem\CreateStockItemUseCase;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStockItem extends CreateRecord
{
    protected static string $resource = StockItemResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $useCase = app(CreateStockItemUseCase::class);
            $item = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Товар успешно создан')
                ->success()
                ->send();

            return \App\Models\StockItem::find($item->getId());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при создании товара')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
