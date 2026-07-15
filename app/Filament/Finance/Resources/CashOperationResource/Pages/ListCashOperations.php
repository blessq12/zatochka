<?php

namespace App\Filament\Finance\Resources\CashOperationResource\Pages;

use App\Filament\Finance\Resources\CashOperationResource;
use Filament\Resources\Pages\ListRecords;

class ListCashOperations extends ListRecords
{
    protected static string $resource = CashOperationResource::class;

    protected function getHeaderActions(): array
    {
        return CashOperationResource::getHeaderActions();
    }
}
