<?php

namespace App\Filament\Resources\RepairPriceItems\Pages;

use App\Filament\Resources\RepairPriceItems\RepairPriceItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairPriceItems extends ListRecords
{
    protected static string $resource = RepairPriceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
