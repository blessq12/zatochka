<?php

namespace App\Filament\Resources\Manager\StockCategoryResource\Pages;

use App\Application\UseCases\Warehouse\StockCategory\CreateStockCategoryUseCase;
use App\Filament\Resources\Manager\StockCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStockCategory extends CreateRecord
{
    protected static string $resource = StockCategoryResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $useCase = app(CreateStockCategoryUseCase::class);
            $category = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Категория товаров успешно создана')
                ->success()
                ->send();

            // Возвращаем Eloquent модель по ID из Domain Entity
            return \App\Models\StockCategory::find($category->getId());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при создании категории товаров')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
