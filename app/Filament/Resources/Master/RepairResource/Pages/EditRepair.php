<?php

namespace App\Filament\Resources\Master\RepairResource\Pages;

use App\Filament\Resources\Master\RepairResource;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepair extends EditRecord
{
    protected static string $resource = RepairResource::class;

    protected array $parts = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Загружаем существующие запчасти из StockMovement
        $parts = [];
        $stockMovements = $this->record->stockMovements()->with('stockItem')->get();

        foreach ($stockMovements as $movement) {
            $parts[] = [
                'stock_item_id' => $movement->stock_item_id,
                'quantity' => $movement->quantity,
                'unit_price' => $movement->unit_price,
                'total_price' => $movement->total_amount,
                'available_stock' => $movement->stockItem->quantity + $movement->quantity, // Возвращаем обратно для отображения
                'description' => $movement->description,
            ];
        }

        $data['parts'] = $parts;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Сохраняем запчасти для последующего использования
        $this->parts = $data['parts'] ?? [];

        // Удаляем parts из данных ремонта, так как это не поле модели Repair
        unset($data['parts']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Получаем старые движения запчастей для возврата стока
        $oldMovements = $this->record->stockMovements;

        // Возвращаем старый сток
        foreach ($oldMovements as $oldMovement) {
            $stockItem = $oldMovement->stockItem;
            if ($stockItem) {
                $stockItem->addStock($oldMovement->quantity);
            }
        }

        // Удаляем старые движения запчастей
        $this->record->stockMovements()->delete();

        // Создаем новые движения запчастей
        if (!empty($this->parts)) {
            foreach ($this->parts as $part) {
                StockMovement::create([
                    'stock_item_id' => $part['stock_item_id'],
                    'movement_type' => StockMovement::TYPE_OUT,
                    'quantity' => $part['quantity'],
                    'repair_id' => $this->record->getKey(),
                    'unit_price' => $part['unit_price'] ?? null,
                    'total_amount' => ($part['unit_price'] ?? 0) * $part['quantity'],
                    'description' => $part['description'] ?? 'Использовано в ремонте',
                    'movement_date' => now(),
                    'created_by' => auth()->id(),
                    // Данные запчасти будут автоматически заполнены в событии creating
                ]);
            }
        }
    }
}
