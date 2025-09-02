<?php

namespace App\Filament\Resources\Master\InventoryTransactionResource\Pages;

use App\Filament\Resources\Master\InventoryTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryTransaction extends CreateRecord
{
    protected static string $resource = InventoryTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
