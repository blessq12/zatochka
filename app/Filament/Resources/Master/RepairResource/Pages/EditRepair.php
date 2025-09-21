<?php

namespace App\Filament\Resources\Master\RepairResource\Pages;

use App\Filament\Resources\Master\RepairResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepair extends EditRecord
{
    protected static string $resource = RepairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Обработка запчастей
        if (isset($data['stock_movements'])) {
            $warehouse = $this->record->order->branch->warehouses()->first();
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
