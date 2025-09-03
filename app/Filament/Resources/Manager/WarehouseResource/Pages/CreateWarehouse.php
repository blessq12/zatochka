<?php

namespace App\Filament\Resources\Manager\WarehouseResource\Pages;

use App\Filament\Resources\Manager\WarehouseResource;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\WarehouseName;
use App\Domain\Inventory\ValueObjects\BranchId;
use App\Domain\Inventory\Services\WarehouseService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        $warehouseService = app(WarehouseService::class);

        $id = WarehouseId::fromString($this->record->id);
        $name = WarehouseName::fromString($this->record->name);
        $branchId = $this->record->branch_id ? BranchId::fromString($this->record->branch_id) : null;

        // Создаем склад через доменный сервис
        $warehouse = $warehouseService->createWarehouse($id, $branchId, $name, $this->record->description);

        Notification::make()
            ->title('Склад создан')
            ->success()
            ->send();
    }
}
