<?php

namespace App\Filament\Resources\Master\RepairResource\Pages;

use App\Filament\Resources\Master\RepairResource;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreateRepair extends CreateRecord
{
    protected static string $resource = RepairResource::class;

    protected array $parts = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Сохраняем запчасти для последующего использования
        $this->parts = $data['parts'] ?? [];

        // Удаляем parts из данных ремонта, так как это не поле модели Repair
        unset($data['parts']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Создаем движения запчастей после создания ремонта
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
