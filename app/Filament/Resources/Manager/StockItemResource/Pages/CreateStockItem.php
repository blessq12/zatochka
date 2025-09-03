<?php

namespace App\Filament\Resources\Manager\StockItemResource\Pages;

use App\Filament\Resources\Manager\StockItemResource;
use App\Domain\Inventory\ValueObjects\StockItemId;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\CategoryId;
use App\Domain\Inventory\ValueObjects\StockItemName;
use App\Domain\Inventory\ValueObjects\SKU;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Inventory\ValueObjects\Unit;
use App\Domain\Inventory\Services\StockItemService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStockItem extends CreateRecord
{
    protected static string $resource = StockItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        $stockItemService = app(StockItemService::class);

        $id = StockItemId::fromString($this->record->id);
        $warehouseId = WarehouseId::fromString($this->record->warehouse_id);
        $categoryId = CategoryId::fromString($this->record->category_id);
        $name = StockItemName::fromString($this->record->name);
        $sku = SKU::fromString($this->record->sku);
        $quantity = Quantity::fromInteger($this->record->quantity ?? 0);
        $minStock = Quantity::fromInteger($this->record->min_stock ?? 0);
        $unit = Unit::fromString($this->record->unit ?? 'шт');

        $purchasePrice = $this->record->purchase_price ? Money::fromFloat($this->record->purchase_price) : null;
        $retailPrice = $this->record->retail_price ? Money::fromFloat($this->record->retail_price) : null;

        // Создаем товар через доменный сервис
        $stockItem = $stockItemService->createStockItem(
            $id,
            $warehouseId,
            $categoryId,
            $name,
            $sku,
            $this->record->description,
            $purchasePrice,
            $retailPrice,
            $quantity,
            $minStock,
            $unit,
            $this->record->supplier,
            $this->record->manufacturer,
            $this->record->model
        );

        Notification::make()
            ->title('Товар создан')
            ->success()
            ->send();
    }
}
