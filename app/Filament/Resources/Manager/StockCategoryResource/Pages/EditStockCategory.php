<?php

namespace App\Filament\Resources\Manager\StockCategoryResource\Pages;

use App\Application\UseCases\Warehouse\StockCategory\UpdateStockCategoryUseCase;
use App\Application\UseCases\Warehouse\StockCategory\DeleteStockCategoryUseCase;
use App\Filament\Resources\Manager\StockCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStockCategory extends EditRecord
{
    protected static string $resource = StockCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->before(function () {
                    try {
                        $useCase = app(DeleteStockCategoryUseCase::class);
                        $useCase->loadData(['id' => $this->record->id])->validate();
                        $useCase->execute();

                        Notification::make()
                            ->title('Категория товаров успешно удалена')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                        $this->halt();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка при удалении категории товаров')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        $this->halt();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            $data['id'] = $this->record->id;

            $useCase = app(UpdateStockCategoryUseCase::class);
            $category = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Категория товаров успешно обновлена')
                ->success()
                ->send();

            // Возвращаем обновленные данные
            return [
                'warehouse_id' => $category->getWarehouseId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'color' => $category->getColor(),
                'sort_order' => $category->getSortOrder(),
                'is_active' => $category->isActive(),
            ];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при обновлении категории товаров')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
