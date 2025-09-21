<?php

namespace App\Filament\Resources\Master\RepairResource\Pages;

use App\Filament\Resources\Master\RepairResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepair extends CreateRecord
{
    protected static string $resource = RepairResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Автоматическое заполнение полей при создании
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Обработка запчастей - получаем склад через заказ
        if (isset($data['stock_movements'])) {
            $order = \App\Models\Order::find($data['order_id']);
            $warehouse = $order?->branch?->warehouses()->first();

            foreach ($data['stock_movements'] as &$movement) {
                $movement['warehouse_id'] = $warehouse?->id;
                $movement['movement_type'] = 'out';
                $movement['movement_date'] = now();
                $movement['created_by'] = auth()->id();
                $movement['total_amount'] = ($movement['quantity'] ?? 0) * ($movement['unit_price'] ?? 0);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
