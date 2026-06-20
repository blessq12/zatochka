<?php

namespace App\Filament\Resources\WarehouseItems\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class EditWarehouseItemRecord extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
