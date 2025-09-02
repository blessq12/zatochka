<?php

namespace App\Filament\Resources\Manager\InventoryResource\Pages;

use App\Filament\Resources\Manager\InventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
