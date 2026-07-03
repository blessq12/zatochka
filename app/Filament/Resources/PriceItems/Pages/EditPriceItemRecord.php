<?php

namespace App\Filament\Resources\PriceItems\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class EditPriceItemRecord extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
