<?php

namespace App\Filament\Resources\SharpeningPriceItems\Pages;

use App\Filament\Resources\SharpeningPriceItems\SharpeningPriceItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSharpeningPriceItems extends ListRecords
{
    protected static string $resource = SharpeningPriceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
