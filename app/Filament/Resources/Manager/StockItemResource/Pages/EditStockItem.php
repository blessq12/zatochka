<?php

namespace App\Filament\Resources\Manager\StockItemResource\Pages;

use App\Filament\Resources\Manager\StockItemResource;
use App\Application\UseCases\Warehouse\StockItem\UpdateStockItemUseCase;
use App\Application\UseCases\Warehouse\StockItem\DeleteStockItemUseCase;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStockItem extends EditRecord
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->before(function () {
                    try {
                        $useCase = app(DeleteStockItemUseCase::class);
                        $useCase->loadData(['id' => $this->record->id])->validate();
                        $useCase->execute();

                        Notification::make()
                            ->title('Товар успешно удален')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                        $this->halt();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка при удалении товара')
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

            $useCase = app(UpdateStockItemUseCase::class);
            $item = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Товар успешно обновлен')
                ->success()
                ->send();

            return [
                'warehouse_id' => $item->getWarehouseId(),
                'category_id' => $item->getCategoryId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'description' => $item->getDescription(),
                'purchase_price' => $item->getPurchasePrice(),
                'retail_price' => $item->getRetailPrice(),
                'quantity' => $item->getQuantity(),
                'min_stock' => $item->getMinStock(),
                'unit' => $item->getUnit(),
                'supplier' => $item->getSupplier(),
                'manufacturer' => $item->getManufacturer(),
                'model' => $item->getModel(),
                'is_active' => $item->isActive(),
            ];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при обновлении товара')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
