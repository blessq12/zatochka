<?php

namespace App\Filament\Resources\Manager\BonusTransactionResource\Pages;

use App\Filament\Resources\Manager\BonusTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonusTransactions extends ListRecords
{
    protected static string $resource = BonusTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Убираем CreateAction - нельзя создавать транзакции напрямую
        ];
    }
}
