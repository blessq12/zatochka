<?php

namespace App\Filament\Resources\PriceItemResource\Pages;

use App\Filament\Resources\PriceItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPriceItem extends EditRecord
{
    protected static string $resource = PriceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
