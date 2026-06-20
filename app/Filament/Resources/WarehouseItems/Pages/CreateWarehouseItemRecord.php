<?php

namespace App\Filament\Resources\WarehouseItems\Pages;

use App\Filament\Support\AbstractWarehouseItemResource;
use Filament\Resources\Pages\CreateRecord;

abstract class CreateWarehouseItemRecord extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var AbstractWarehouseItemResource $resource */
        $resource = static::getResource();

        $data['type'] = $resource::warehouseItemType();
        $data['quantity'] = 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
