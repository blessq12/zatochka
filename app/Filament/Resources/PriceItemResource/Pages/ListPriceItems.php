<?php

namespace App\Filament\Resources\PriceItemResource\Pages;

use App\Filament\Resources\PriceItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriceItems extends ListRecords
{
    protected static string $resource = PriceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
