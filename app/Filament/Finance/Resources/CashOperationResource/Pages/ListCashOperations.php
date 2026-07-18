<?php

namespace App\Filament\Finance\Resources\CashOperationResource\Pages;

use App\Filament\Finance\Pages\CashDeskDashboard;
use App\Filament\Finance\Resources\CashOperationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCashOperations extends ListRecords
{
    protected static string $resource = CashOperationResource::class;

    protected static ?string $title = 'Кассовые операции';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToCashDesk')
                ->label('К кассе')
                ->url(CashDeskDashboard::getUrl())
                ->link(),
        ];
    }
}
